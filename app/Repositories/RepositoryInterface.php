<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Get one
     * @param $id
     * @return mixed
     */
    public function find($id);
    /**
     * get model
     * @return string
     */
    public function getModel();
}
