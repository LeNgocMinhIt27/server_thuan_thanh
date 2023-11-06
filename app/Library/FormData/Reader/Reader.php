<?php

namespace App\Library\FormData\Reader;

use App\Library\FormData\Reader\Custom\ExcelReader;
use App\Library\FormData\Reader\Custom\GeojsonReader;
use App\Library\FormData\Reader\Custom\JsonReader;
use App\Library\FormData\Reader\Custom\ShapeFileReader;
use ErrorException;
use File;

class Reader
{
    private $product;
    public function __construct($type, array $files)
    {
        switch ($type) {
            case 'csv':
            case 'excel':
                $this->product = new ExcelReader($files);
                break;
            case 'json':
                $this->product = new JsonReader($files);
                break;
            case 'shapefile':
                $this->product = new ShapeFileReader($files);
                break;
            case 'geojson':
                $this->product = new GeojsonReader($files);
                break;

            default:
                throw new ErrorException('Not found parser for file type: ' . $type);
        }
    }

    public function getFields()
    {
        $result = $this->product->getFields();
        return $result;
    }
    public function getRecords()
    {
        $result = $this->product->getRecords();
        return $result;
    }
    public function getTotal()
    {
        $result = $this->product->getTotal();
        return $result;
    }
}
