<?php

namespace App\Helpers\System;

use App\Helpers\ZipHelper;
use Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BackupHelper
{
    public static function backup(string $file_name, $cb_show = null, $options = [])
    {
        $with_db = Arr::get($options, 'with_db', false);
        $with_file = Arr::get($options, 'with_file', false);
        if (!isset($cb_show)) {
            $cb_show = function ($mes) {
            };
        }
        $cb_show('Backup start');
        $dir_temp = 'temp-backup-' . $file_name;
        $full_dir_temp = storage_path('temp/' . $dir_temp);
        File::makeDirectory($full_dir_temp, 0755, false, true);
        File::makeDirectory($full_dir_temp . '/db-dumps', 0755, false, true);
        $config_db = DatabaseHelper::getConfig();
        if ($with_db) {
            $cb_show('Backup database in temp dir: ' . $dir_temp);
            $cmd_backup = 'cd ' . config('app.postgres_dir') . ' && pg_dump --file "' . $full_dir_temp . '/db-dumps/gtvtqs-backup.sql" '
                . '-d "' . DatabaseHelper::getConnectionString() . '" --format=c --blobs';
            shell_exec($cmd_backup);
            $cb_show('Backup database to temp dir done');
        }

        $zipFileName = $file_name . '.zip';
        $path = config('backup.backup.name') . '/' . $zipFileName;
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

        $cb_show('Backup in disk: ' . config('backup.backup.destination.disks')[0]);

        $cb_show('Location file backup: ' . $disk->path($path));

        $cb_show('Location temp dir: ' . $dir_temp);
        $cb_show('Location full temp dir: ' . $full_dir_temp);


        if ($with_file) {
            $filesBackup = config('backup.backup.source.files.include');
            foreach ($filesBackup as $f) {
                $basePath = explode(base_path(), $f);
                if (!File::isFile($f)) {
                    File::copyDirectory($f, $full_dir_temp . '/files/' . $basePath[1]);
                    $cb_show('Copy folder: ' . $f);
                } else {
                    File::copy($f, $full_dir_temp . '/files/' . $basePath[1]);
                    $cb_show('Copy file: ' . $f);
                }
            }
        }

        $zip = new ZipHelper($cb_show);
        $zip->addFolder($full_dir_temp);
        $zip->zip($disk->path('') . '/' . $path);
        $cb_show('Add data to zip done');
        File::deleteDirectory($full_dir_temp);
        $cb_show('Backup done');

        return [
            'name' => $zipFileName,
            'path' => $path,
            'full_path' => $disk->path($path)
        ];
    }
}
