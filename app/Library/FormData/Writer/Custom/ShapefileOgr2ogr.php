<?php

namespace App\Library\FormData\Writer\Custom;

use App\Exports\DynamicTableExport;
use App\Library\FormData\Writer\BaseWriter;
use Carbon\Carbon;
use Excel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShapefileOgr2ogr extends BaseWriter
{
    /**
     * Return the icon storage disk.
     *
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    private function disk()
    {
        return Storage::disk('temp');
    }
    public function write($items, $headers, $fileName = 'data', $options = [])
    {
        $disk = $this->disk();
        $fileName = Str::slug($fileName);
        $ogr2ogr = config('app.ogr2ogr');
        $name_file = $fileName . '-' . Carbon::now()->format('YmdHs');
        $folder_name = $name_file;
        $full_name_file = $folder_name . '/' . $name_file;
        $zip_name_file = $fileName . '-' . Carbon::now()->format('YmdHs') . '.zip';
        $zip_path = $disk->path($zip_name_file);
        if (!file_exists($disk->path($folder_name))) {
            $disk->makeDirectory($folder_name, 0777, true, true);
            // path does not exist
        }
        $table_name =  "_check_table_" . time();
        try {
            $field_geometry = 'geometry';
            if (isset($options['field_geometry'])) {
                $field_geometry = $options['field_geometry'];
            }
            if (($key = array_search($field_geometry, $headers)) !== false) {
                unset($headers[$key]);
            }
            Schema::create($table_name, function (Blueprint $table) use ($headers, $field_geometry) {
                $table->id();
                $table->geometry($field_geometry);
                foreach ($headers as $header) {
                    $table->text($header)->nullable();
                }
                // $table->temporary();
            });
            DB::table($table_name)->insert($items->map(function ($item) use ($field_geometry) {
                $item[$field_geometry] = $this->handleGeometry($item[$field_geometry]);
                return $item;
            })->toArray());
            $config_db = DB::connection()->getConfig();
            $database = DB::connection()->getDatabaseName();
            $command_import =  $ogr2ogr . ' -f "ESRI Shapefile" "' . $disk->path($full_name_file . '.shp') . '" PG:"host=' . $config_db['host'] . ' port=' . $config_db['port'] . ' user=' . $config_db['username'] . ' dbname=' . $database . ' password=' . $config_db['password'] . '" -sql "select * from ' . $table_name . '" -lco ENCODING=UTF-8';
            $result = exec($command_import, $output, $return);
            $zip = new \ZipArchive();
            $zip->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            foreach (['shp', 'shx', 'prj', 'dbf', 'cpg', 'dbt'] as  $value) {
                $zip->addFile($disk->path($full_name_file . '.' . $value), $full_name_file . '.' . $value);
            }
            $zip->close();
        } finally {
            Schema::dropIfExists($table_name);
            $disk->deleteDirectory($folder_name);
        }
        return $zip_name_file;
    }
    public function handleGeometry($geometry)
    {
        if (!is_string($geometry)) {
            $geometry = json_encode($geometry);
        }
        return DB::raw('ST_Transform(ST_GeomFromGeoJSON(\'' . $geometry . '\'),4326)');
    }
}
