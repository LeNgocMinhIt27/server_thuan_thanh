<?php

namespace App\Library\FormData\Reader\Custom;

use Illuminate\Support\Facades\Storage;
use Shapefile\Shapefile;
use Shapefile\ShapefileReader as LibaryShapefileReader;

class ShapeFileReaderLocal implements IHandleFile
{
    protected $path;
    protected $records;
    protected $headers;
    protected $total;
    public function __construct(string $path)
    {
        $this->path = $path;
        $result = $this->handleShapeFile($path, function ($shapefile) {
            return [
                'fields' => $shapefile->getFieldsNames(),
                'total' => $shapefile->getTotRecords(),
            ];
        });
        $this->headers = $result['fields'];
        $this->headers[] = 'geometry';
        $this->total = $result['total'];
    }
    public function getFields()
    {
        return $this->headers;
    }
    public function getRecords()
    {
        return $this->handleShapeFile(
            $this->path,
            function ($shapefile) {
                $records = [];
                foreach ($shapefile as $record) {
                    if ($record->isDeleted()) {
                        continue;
                    }
                    $records[] = array_merge(
                        [
                            'geometry' => $record->getGeoJSON()
                        ],
                        $record->getDataArray()
                    );
                }
                return $records;
            }
        );
    }
    public function getTotal()
    {
        return $this->total;
    }
    public function readLocalFile(string $path, $cb)
    {
        try {
            $shape_file_reader = new LibaryShapefileReader(
                $path,
                [
                    Shapefile::OPTION_DBF_CONVERT_TO_UTF8 => false,
                ]
            );
            $return = $cb($shape_file_reader);
            $shape_file_reader = null;
            return $return;
        } catch (\Throwable $th) {
            try {
                $shape_file_reader = null;
            } catch (\Throwable $e) {
            }
            throw $th;
        }
    }
    public function handleShapeFile($path, $cb)
    {
        try {
            $shape_file_reader = new LibaryShapefileReader(
                $path,
                [
                    Shapefile::OPTION_DBF_CONVERT_TO_UTF8 => false,
                ]
            );
            $return = $cb($shape_file_reader);
            $shape_file_reader = null;
            return $return;
        } catch (\Throwable $th) {
            try {
                $shape_file_reader = null;
            } catch (\Throwable $e) {
            }
            throw $th;
        }
    }
}
