<?php

namespace App\Library\FormData\Reader\Custom;

interface IHandleFile
{
    public function getFields();
    public function getRecords();
    public function getTotal();
}
