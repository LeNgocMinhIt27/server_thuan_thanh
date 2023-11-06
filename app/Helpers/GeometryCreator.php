<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use MStaack\LaravelPostgis\Geometries\GeometryCollection;
use MStaack\LaravelPostgis\Geometries\LineString;
use MStaack\LaravelPostgis\Geometries\MultiLineString;
use MStaack\LaravelPostgis\Geometries\Point;
use MStaack\LaravelPostgis\Geometries\MultiPoint;
use MStaack\LaravelPostgis\Geometries\MultiPolygon;
use MStaack\LaravelPostgis\Geometries\Polygon;

class GeometryCreator
{
    static public function create($geometry)
    {
        if (empty($geometry) || empty($geometry['type']) || empty($geometry['coordinates'])) {
            return null;
        }
        switch ($geometry['type']) {
            case 'Point':
                return new Point($geometry['coordinates'][1], $geometry['coordinates'][0]);
            case 'MultiPoint':
                return new MultiPoint(
                    array_map(function ($coordinates) {
                        return new Point($coordinates[1], $coordinates[0]);
                    }, $geometry['coordinates'])
                );
            case 'LineString':
                return new LineString(
                    array_map(function ($coordinates) {
                        return new Point($coordinates[1], $coordinates[0]);
                    }, $geometry['coordinates'])
                );
            case 'MultiLineString':
                return new MultiLineString(
                    array_map(function ($lineString) {
                        return new LineString(
                            array_map(function ($coordinates) {
                                return new Point($coordinates[1], $coordinates[0]);
                            }, $lineString)
                        );
                    }, $geometry['coordinates'])
                );
            case 'Polygon':
                return new Polygon(
                    array_map(function ($lineString) {
                        return new LineString(
                            array_map(function ($coordinates) {
                                return new Point($coordinates[1], $coordinates[0]);
                            }, $lineString)
                        );
                    }, $geometry['coordinates'])
                );
            case 'MultiPolygon':
                return new MultiPolygon(
                    array_map(function ($polygon) {
                        return new Polygon(
                            array_map(function ($lineString) {
                                return new LineString(
                                    array_map(function ($coordinates) {
                                        return new Point($coordinates[1], $coordinates[0]);
                                    }, $lineString)
                                );
                            }, $polygon)
                        );
                    }, $geometry['coordinates'])
                );
            case 'GeometryCollection':
                return new GeometryCollection(
                    array_map(function ($geometry) {
                        return GeometryCreator::create($geometry);
                    }, $geometry['geometries'])
                );
        }
    }

    static public function fake($type)
    {
        if ($type === 'MStaack\LaravelPostgis\Geometries\Polygon') {
            return Polygon::fromString('POLYGON ((105.27099609375 20.704738720055513, 106.34765625 20.704738720055513, 106.34765625 21.46329344189928, 105.27099609375 21.46329344189928, 105.27099609375 20.704738720055513))');
        } else if ($type === 'MStaack\LaravelPostgis\Geometries\Point') {
            return new Point(20.96656916027155, 106.09222412109375, 0);
        } else {
            throw new \Exception('Not support ' . $type);
        }
    }

    static public function fakeRaw($type)
    {
        $wkt = null;

        if ($type === 'MStaack\LaravelPostgis\Geometries\Polygon') {
            $wkt = 'POLYGON ((105.27099609375 20.704738720055513, 106.34765625 20.704738720055513, 106.34765625 21.46329344189928, 105.27099609375 21.46329344189928, 105.27099609375 20.704738720055513))';
        } else if ($type === 'MStaack\LaravelPostgis\Geometries\Point') {
            $wkt = 'POINT (106.09222412109375 20.96656916027155)';
        } else {
            throw new \Exception('Not support ' . $type);
        }

        return DB::raw("ST_GeomFromText('$wkt')");
    }
}
