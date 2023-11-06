<?php

namespace App\Helpers\System;

use App\Helpers\ZipHelper;
use Arr;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RestoreHelper
{
    public static function restore($file, $cb_show = null, $options = [])
    {
        $with_db = Arr::get($options, 'with_db', false);
        $with_file = Arr::get($options, 'with_file', false);
        if (!isset($cb_show)) {
            $cb_show = function ($mes) {
            };
        }
        if (!File::exists($file)) {
            throw new Exception("Không tồn tại file backup");
        }
        $cb_show('Restore start');
        $cb_show('Location file restore: ' . $file);

        $dir_temp = 'temp-backup-' . Carbon::now()->format('Y-m-d-H-i-s');

        $storageDestinationPath = storage_path('temp/' . $dir_temp);


        $cb_show('Location temp dir: ' . $dir_temp);
        $cb_show('Location full temp dir: ' . $storageDestinationPath);

        File::makeDirectory($storageDestinationPath, 0755, false, true);
        $zip = new ZipHelper();
        $zip->unzip($file, $storageDestinationPath);

        $cb_show('Unzip data done');

        if ($with_file) {
            $folder_data = $storageDestinationPath . "/files";
            $data_remove = config('backup.backup.source.files.include');
            $path_data_remove = array();

            foreach ($data_remove as $d) {
                if (File::exists($folder_data . '/' . explode(base_path(), $d)[1])) {
                    if (!RestoreHelper::empty_dir($folder_data . '/' . explode(base_path(), $d)[1])) {
                        array_push($path_data_remove, explode(base_path(), $d)[1]);
                    }
                }
            }

            foreach ($path_data_remove as $p) {
                if (!File::isFile(base_path($p))) {
                    File::deleteDirectory(base_path($p));
                    $cb_show('Delete folder: ' . base_path($p));
                } else {
                    File::delete(base_path($p));
                    $cb_show('Delete file: ' . base_path($p));
                }
            }
            $cb_show('Copy file begin');
            File::copyDirectory($folder_data, base_path());
            $cb_show('Copy file done');
        }

        if ($with_db) {
            $files_scan = scandir($storageDestinationPath . "/db-dumps");
            $DBFileRestore = $storageDestinationPath . "/db-dumps" . "/" . $files_scan[2];

            $output = null;

            $cb_show('Restore database begin');


            $config_db = DB::connection()->getConfig();

            if (File::exists($DBFileRestore)) {
                $db_tables = DB::select(
                    "SELECT n.nspname AS name
                    FROM pg_catalog.pg_namespace n
                    WHERE n.nspname !~ '^pg_' AND n.nspname <> 'information_schema'"
                );
                foreach ($db_tables as $schema) {

                    $cb_show('Delete schema: ' . $schema->name);
                    DB::statement('DROP SCHEMA IF EXISTS ' . $schema->name . ' CASCADE;');
                }
                DB::statement('CREATE SCHEMA public;');

                $cb_show('Run fil sql begin');

                exec(
                    'cd ' . config('app.postgres_dir') . ' && pg_restore -e -d "postgres://' . $config_db['username'] . ':' . $config_db['password'] . '@' . $config_db['host'] . ':' . $config_db['port'] . '/' . DB::connection()->getDatabaseName() . '" ' . $DBFileRestore,
                    $output
                );
            } else {
                $cb_show('File db does not exist');
            }
            $cb_show('Restore database done');
        }

        // DB::statement('DELETE FROM jobs');

        File::deleteDirectory($storageDestinationPath);
        $cb_show('Restore done');
    }

    public static function empty_dir($path)
    {
        $files_in_dir = scandir($path);
        $items_count = count($files_in_dir);
        if ($items_count <= 2) return true;
        return false;
    }
}
