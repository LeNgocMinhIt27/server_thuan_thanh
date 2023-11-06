<?php

namespace App\Library\FormData\Reader\Custom;

use Illuminate\Support\Facades\Storage;
use Shapefile\Shapefile;
use Shapefile\ShapefileReader as LibaryShapefileReader;

class ShapeFileReader implements IHandleFile
{
    protected $files;
    protected $records;
    protected $headers;
    protected $total;
    public function __construct(array $files)
    {
        $this->files = $files;
        $result = $this->handleShapeFile($files, function ($shapefile) {
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
            $this->files,
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
    private function isVectorFormat($files): bool
    {
        $extensions = [];
        foreach ($files as $file) {
            $extensions[] = pathinfo($file->getClientOriginalName())['extension'];
        }

        return empty(array_diff(['shp', 'shx', 'dbf'], $extensions));
    }

    public function handleShapeFile($files, $cb)
    {
        $request = request();
        if (!$this->isVectorFormat($files)) {
            abort(400, "Thiếu tập tin, cần các tập tin có đuôi .dbf, .shp, .shx");
        }
        $folder = time() . '_upload_shape_file';
        if ($request->user()) {
            $folder = $request->user()->id . '_' . $folder;
        }
        $name_shp = '';
        foreach ($files as $file) {
            $ext = $file->getClientOriginalExtension();
            if ($ext == 'shp') {
                $name_shp = $file->getClientOriginalName();
            }
            Storage::disk('temp')->putFileAs($folder, $file, $file->getClientOriginalName());
        }
        try {
            $shape_file_reader = new LibaryShapefileReader(
                Storage::disk('temp')->path($folder . '/' . $name_shp),
                [
                    Shapefile::OPTION_DBF_CONVERT_TO_UTF8 => false,
                ]
            );
            $return = $cb($shape_file_reader);
            $shape_file_reader = null;
            Storage::disk('temp')->deleteDirectory($folder);
            return $return;
        } catch (\Throwable $th) {
            try {
                $shape_file_reader = null;
                Storage::disk('temp')->deleteDirectory($folder);
            } catch (\Throwable $e) {
            }
            throw $th;
        }
    }
}
