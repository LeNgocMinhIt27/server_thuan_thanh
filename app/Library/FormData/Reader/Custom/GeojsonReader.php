<?php

namespace App\Library\FormData\Reader\Custom;

class GeojsonReader implements IHandleFile
{
    protected $files;
    protected $records;
    protected $headers;
    protected $total = 0;
    public function __construct(array $files)
    {
        $this->files = $files;
        $data = $this->getFieldInGeoJson($files[0]);
        $this->headers = $data['columns'];
        $this->headers[] = 'geometry';
        $this->total = $data['total'];
        $this->records = $data['records'];
    }
    public function getFields()
    {
        return $this->headers;
    }
    public function getRecords()
    {
        $data = [];
        foreach ($this->records as $index => $record) {
            $data[] = array_combine($this->headers, $record);
        }
        return $data;
    }
    public function getTotal()
    {
        return $this->total;
    }

    public function getFieldInGeoJson($file)
    {
        $string = file_get_contents($file);
        $columns = [];
        $total = 0;
        $records = [];
        $json_a = json_decode($string, true);
        if ($json_a['type'] === 'FeatureCollection') {
            $columns = array_keys($json_a['features'][0]['properties']);
            $total = count($json_a['features']);
            foreach ($json_a['features'] as  $record) {
                $records[] = array_merge(
                    $record['properties'],
                    [
                        'geometry' => $record['geometry']
                    ]
                );
            }
        }
        return ['columns' => $columns, 'total' => $total, 'records' => $records];
    }
}
