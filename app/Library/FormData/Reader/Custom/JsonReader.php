<?php

namespace App\Library\FormData\Reader\Custom;


class JsonReader implements IHandleFile
{
    protected $records;
    protected $headers;
    public function __construct(array $files)
    {
        $strJsonFileContents = file_get_contents($files[0]);
        // Convert to array
        $data = json_decode($strJsonFileContents, true) ?? [];
        $this->headers = array_keys($data[0]);
        $this->records = $data;
    }
    public function getFields()
    {
        return $this->headers;
    }
    public function getRecords()
    {
        $data = [];
        foreach ($this->records as $index => $record) {
            if ($index === 0) {
                continue;
            }
            $data[] = array_combine($this->headers, $record);
        }
        return $data;
    }
    public function getTotal()
    {
        return count($this->records);
    }
}
