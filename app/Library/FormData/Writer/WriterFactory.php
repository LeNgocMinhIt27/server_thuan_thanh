<?php

namespace App\Library\FormData\Writer;

use App\Library\FormData\Writer\Custom\CsvWriter;
use App\Library\FormData\Writer\Custom\ExcelWriter;
use App\Library\FormData\Writer\Custom\GeoJsonWriter;
use App\Library\FormData\Writer\Custom\JsonWriter;
use App\Library\FormData\Writer\Custom\ShapefileOgr2ogr;
use App\Library\FormData\Writer\Custom\ShapefileWriter;
use ErrorException;

class WriterFactory
{
    public static function getByType($ext)
    {
        switch ($ext) {
            case 'excel':
                return new ExcelWriter();
            case 'csv':
                return new CsvWriter();
            case 'json':
                return new JsonWriter();
            case 'geojson':
                return new GeoJsonWriter();
            case 'shapefile':
                return new ShapefileWriter();
            case 'shapefileOgr2Ogr':
                return new ShapefileOgr2ogr();
            default:
                throw new ErrorException('Not support: ' . $ext);
        }
    }
}
