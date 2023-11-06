<?php

namespace App\Http\Middleware;

use App;
use Carbon\Carbon;
use Closure;
use DB;
use Jenssegers\Agent\Agent;

class Tracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $type = 'view-home')
    {
        $ip = $this->getIp();
        $agent = new Agent();
        if ($agent->isDesktop()) {
            $device = 'desktop';
        } else if ($agent->isPhone()) {
            $device = 'phone';
        } else if ($agent->isTablet()) {
            $device = 'tablet';
        } else {
            $device = "";
        }
        $browser = $agent->browser();
        $platform = $agent->platform();
        $is_tracking = DB::table('tracking_views')
            ->where('ip', $ip)
            ->where('device', $device)
            ->where('browser', $browser)
            ->where('platform', $platform)
            ->where('date_time', '>=', Carbon::now()->subMinute(15))->exists();

        $is_tracking = DB::table('ip_infos')
            ->where('ip', $ip)->exists();
        if (!$is_tracking) {
            $position = \Location::get($ip);
            if ($position) {
                $location = $position->toArray();
                $province_id = null;
                if (!empty($location['longitude']) && !empty($location['latitude'])) {
                    $province = DB::selectOne("select id from provinces where ST_Contains( geometry::geometry,ST_SetSRID(ST_Point(" . $location['longitude'] . "," . $location['latitude'] . "), 4326))");
                    if (isset($province)) {
                        $province_id = $province->id;
                    }
                }
                DB::table('ip_infos')->insert([
                    'ip' => $ip,
                    'country_name' => $location['countryName'],
                    'country_code' => $location['countryCode'],
                    'region_name' => $location['regionName'],
                    'region_code' => $location['regionCode'],
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'province_id' => $province_id
                ]);
            }
        }
        DB::table('tracking_views')->insert([
            'date_time' => Carbon::now(),
            'ip' => $ip,
            'type' => $type,
            'browser' =>  $browser,
            'device' => $device,
            'platform' => $platform,
        ]);
        return $next($request);
    }
    public function getIp()
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
            return '118.70.179.42';
        }
        return request()->ip(); // it will return server ip when no client ip found
    }
}
