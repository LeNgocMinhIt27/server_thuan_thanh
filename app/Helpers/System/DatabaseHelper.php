<?php

namespace App\Helpers\System;

use App;
use Illuminate\Support\Facades\DB;
use Str;

class DatabaseHelper
{
    /**
     * Return the icon storage disk.
     *
     * @return \Illuminate\Database\Connection
     */
    public static function connection()
    {
        return DB::connection();
    }
    public static function getConfig()
    {
        return DatabaseHelper::connection()->getConfig();
    }
    public static function getConnectionString()
    {
        $config_db = DatabaseHelper::connection()->getConfig();
        $db_name = DatabaseHelper::connection()->getDatabaseName();
        return 'postgres://' . $config_db['username'] . ':' . $config_db['password'] . '@' . $config_db['host'] . ':' . $config_db['port'] . '/' . $db_name;
    }
    public static function createViewFromQuery($query)
    {
        $query_str = str_replace('?', "'%s'", $query->toSql());
        $query_str = vsprintf($query_str, $query->getBindings());
        $user_id = request()->user()->getKey() ?? 'system';
        // if (App::isLocal()) {
        //     $table_view = 'view_extra_' .  $user_id;
        // } else {
        $table_view = 'view_extra_' . time() . '_' . $user_id . '_' . str_replace('-', '', Str::uuid());
        // }
        DB::unprepared('DROP  VIEW IF EXISTS ' . $table_view . ' CASCADE;');

        DB::statement("CREATE VIEW " . $table_view . " as " . $query_str);
        return $table_view;
    }
}
