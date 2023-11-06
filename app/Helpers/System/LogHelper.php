<?php

namespace App\Helpers\System;

use App\Models\Auth\User;
use App\Models\IpInfo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use Spatie\Activitylog\Contracts\Activity;
use Stevebauman\Location\Facades\Location;

class LogHelper
{
    public static function log($log_name, $description, $properties, $method, $subject = null, $causer = null, $trans_properties = [])
    {
        $subject_display = null;
        if (isset($subject))
            $subject_display = $subject->getCauserDisplay();
        if (isset($causer))
            $causer_display = $causer->getCauserDisplay();
        return LogHelper::logOption([
            'logname' => $log_name ?? 'system',
            'method' => $method,
            'description' => $description,
            'properties' => $properties,
            'subject' => $subject,
            'causer' => $causer,
            'subject_display' => $subject_display ?? null,
            'causer_display' => $causer_display ?? null,
            'trans_properties' => $trans_properties
        ]);
    }

    public static function logOption($option, $cb = null): ?Activity
    {
        $log = activity($option['logname']);
        if (!empty($option['causer'])) {
            $log->by($option['causer']);
        } else {
            $log->by(request()->user());
        }
        if (isset($option["subject"])) {
            $log->on($option["subject"]);
        }
        $log
            ->tap(function (Activity $activity) use ($option, $cb) {
                $activity->trans_properties = (object)($option['trans_properties'] ?? []);
                $activity->method = $option['method'];
                $causer = $activity->causer;
                $subject = $activity->subject;
                $subject_display = '';
                $causer_display = '';
                if (isset($option['subject_display']))
                    $subject_display = $option['subject_display'];
                else if (isset($subject) && method_exists($subject, 'getCauserDisplay')) {
                    $subject_display = $subject->getCauserDisplay();
                } else if (isset($subject->name)) {
                    $subject_display = $subject->name;
                }
                $activity->subject_display = $subject_display;
                if (isset($option['causer_display']))
                    $causer_display = $option['causer_display'];
                else if (isset($causer) && method_exists($subject, 'getCauserDisplay')) {
                    $causer_display = $causer->getCauserDisplay();
                } else if (isset($causer->name)) {
                    $causer_display = $causer->name;
                }
                $activity->ip = LogHelper::getIp();
                $activity->causer_display = empty($causer_display) ? 'System' : $causer_display;
                if (isset($cb))
                    $cb($activity);
            })
            ->withProperties($option["properties"]);
        return $log->log($option['description']);
    }
    public static function logLogin(User $user, $description = "login", $subject_display = null)
    {
        $agent = new Agent();
        $ip = LogHelper::getIp();

        $position = Location::get($ip);
        $location = [];
        if ($position) {
            $location = $position->toArray();
        }
        $properties = [
            'ip' => $ip,
            'ip-properties' => $location,
            'user-agent' => request()->header('User-Agent'),
            'user-agent-properties' => [
                'browser' => $agent->browser(),
                'platform' => $agent->platform(),
                'device' => $agent->device(),
                'languages ' => $agent->languages(),
            ],
        ];
        LogHelper::logOption([
            'logname' => 'login',
            'method' => 'login',
            'description' => $description,
            'properties' => $properties,
            'causer' => $user,
            'subject_display' => $subject_display ?? null,
        ]);
    }
    public static function getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        if (App::environment('local')) {
            return '42.113.143.223';
        }
        return request()->ip(); // it will return server ip when no client ip found
    }
    public static function getIpInfo()
    {
        $ip = LogHelper::getIp();
        $ip_info = IpInfo::find($ip);
        if (!isset($ip_info)) {
            $position = Location::get($ip);
            if ($position) {
                $location = $position->toArray();
                $province_id = null;
                if (!empty($location['longitude']) && !empty($location['latitude'])) {
                    $province = DB::selectOne("select id from provinces where ST_Contains( geometry::geometry,ST_SetSRID(ST_Point(" . $location['longitude'] . "," . $location['latitude'] . "), 4326))");
                    if (isset($province)) {
                        $province_id = $province->id;
                    }
                }
                $data = [
                    'ip' => $ip,
                    'country_name' => $location['countryName'],
                    'country_code' => $location['countryCode'],
                    'region_name' => $location['regionName'],
                    'region_code' => $location['regionCode'],
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'province_id' => $province_id
                ];
                $ip_info = IpInfo::create($data);
            }
        }
        return $ip_info;
    }
}
