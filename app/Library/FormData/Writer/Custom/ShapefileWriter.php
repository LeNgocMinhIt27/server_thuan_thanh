<?php

namespace App\Library\FormData\Writer\Custom;

use App\Library\FormData\Writer\BaseWriter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Shapefile\Geometry\Linestring;
use Shapefile\Geometry\MultiLinestring;
use Shapefile\Geometry\MultiPoint;
use Shapefile\Geometry\MultiPolygon;
use Shapefile\Geometry\Point;
use Shapefile\Geometry\Polygon;
use Shapefile\Shapefile;
use Shapefile\ShapefileWriter as BaseShapefileWriter;



class ShapefileWriter extends BaseWriter
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
    public function write($datas, $headers, $fileName = 'data', $options = [])
    {
        $disk = $this->disk();
        $headers = array_map(function ($header) {
            return  $header['value'] ?? $header;
        }, $headers);
        try {
            $name_file = $fileName . '-' . Carbon::now()->format('YmdHs');
            $folder_name = $name_file;
            $full_name_file = $folder_name . '/' . $name_file;
            $zip_name_file = $fileName . '-' . Carbon::now()->format('YmdHs') . '.zip';
            $zip_path = $disk->path($zip_name_file);
            // Open Shapefile

            $disk->makeDirectory($folder_name);

            $Shapefile = new BaseShapefileWriter(
                [
                    Shapefile::FILE_SHP => fopen($disk->path($full_name_file . '.shp'), 'c+b'),
                    Shapefile::FILE_SHX => fopen($disk->path($full_name_file . '.shx'), 'c+b'),
                    Shapefile::FILE_DBF => fopen($disk->path($full_name_file . '.dbf'), 'c+b'),
                    Shapefile::FILE_DBT => fopen($disk->path($full_name_file . '.dbt'), 'c+b'),
                    Shapefile::FILE_PRJ => fopen($disk->path($full_name_file . '.prj'), 'c+b'),
                    Shapefile::FILE_CPG => fopen($disk->path($full_name_file . '.cpg'), 'c+b')
                ],
                [
                    Shapefile::OPTION_CPG_ENABLE_FOR_DEFAULT_CHARSET => true,
                    Shapefile::OPTION_ENFORCE_GEOMETRY_DATA_STRUCTURE => false,
                    Shapefile::OPTION_CPG_ENABLE_FOR_DEFAULT_CHARSET => true
                ]
            );
            $Shapefile->setCharset('UTF-8');
            // FIXME : fix cung wkt for 4326
            $Shapefile->setPRJ('GEOGCS["GCS_WGS_1984",DATUM["D_WGS_1984",SPHEROID["WGS_1984",6378137.0,298.257223563]],PRIMEM["Greenwich",0.0],UNIT["Degree",0.0174532925199433]]');
            $field_geometry = 'geometry';
            if (isset($options['field_geometry'])) {
                $field_geometry = $options['field_geometry'];
            }
            if (($key = array_search($field_geometry, $headers)) !== false) {
                unset($headers[$key]);
            }
            $geojson = $datas[0][$field_geometry];
            $Shapefile->setShapeType($this->getTypeGeoJson($geojson));
            // Create field structure
            $header_after = [];
            foreach ($headers as $header) {
                $header_after[] =  $Shapefile->addCharField($header, 254);
            }
            foreach ($datas as $i => $data) {
                $geometry = $this->handleGeoJson($data[$field_geometry]);
                $geometry->initFromGeoJSON(json_encode($data[$field_geometry]));
                foreach ($headers as $i => $header) {
                    if (isset($data[$header])) {
                        $geometry->setData($header_after[$i], $data[$header]);
                    }
                }
                $Shapefile->writeRecord($geometry);
            }
            // $disk->put($full_name_file . '.cpg', 'UTF-8');
            // Finalize and close files to use them
            $Shapefile = null;

            $zip = new \ZipArchive();
            $zip->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            foreach (['shp', 'shx', 'prj', 'dbf', 'cpg', 'dbt'] as  $value) {
                $zip->addFile($disk->path($full_name_file . '.' . $value), $full_name_file . '.' . $value);
            }
            $zip->close();
            $disk->deleteDirectory($folder_name);
            return $zip_name_file;
        } catch (\Throwable $th) {
            try {
                $Shapefile = null;
                $disk->deleteDirectory($folder_name);
            } catch (\Throwable $th2) {
                //throw $th;
            }
            throw $th;
        }
    }
    private function getTypeGeoJson($geojson)
    {
        switch ($geojson['type']) {
            case 'Polygon':
                return Shapefile::SHAPE_TYPE_POLYGON;
            case 'MultiPolygon':
                return Shapefile::SHAPE_TYPE_POLYGON;
            case 'LineString':
                return Shapefile::SHAPE_TYPE_POLYLINE;
            case 'MultiLineString':
                return Shapefile::SHAPE_TYPE_POLYLINE;
            case 'Point':
                return Shapefile::SHAPE_TYPE_POINT;
            case 'MultiPoint':
                return Shapefile::SHAPE_TYPE_MULTIPOINT;
        }
    }
    private function handleGeoJson($geojson)
    {
        switch ($geojson['type']) {
            case 'Polygon':
                return new Polygon();
            case 'MultiPolygon':
                return new MultiPolygon();
            case 'LineString':
                return new Linestring();
            case 'MultiLineString':
                return new MultiLinestring();
            case 'Point':
                return new Point();
            case 'MultiPoint':
                return new MultiPoint();
        }
    }
}
