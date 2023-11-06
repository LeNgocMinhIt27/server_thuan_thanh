<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BaseRepository extends EloquentRepository
{
    private $class;
    public function __construct(string $class, $defaultOption = [])
    {
        $this->class = $class;
        parent::__construct();
        $this->setDefaultOption(array_merge(['logname' => 'system', 'description' => '', 'forceLog' => false, 'disableLog' => false], $defaultOption));
    }
    public function getModel()
    {
        return $this->class;
    }
    public function create(array $attributes, callable $cb = null, $action_log = 'created'): Model
    {
        return DB::transaction(
            function () use ($attributes, $cb, $action_log) {
                return $this->withLogHandler('created', ['description' => $this->defaultOption['logname'] . '.' . $action_log], function ($model) use ($attributes, $cb) {
                    $model = $model->create($attributes);
                    if (isset($cb)) {
                        $cb($model);
                    }
                    return $model;
                });
            }
        );
    }

    public function update($id, array $attributes, callable $cb = null, $action_log = 'updated'): Model
    {
        return DB::transaction(
            function () use ($id, $attributes, $cb, $action_log) {
                return $this->withLogHandler('updated', ['description' => $this->defaultOption['logname'] . '.' . $action_log, 'id' => $id], function ($model) use ($attributes, $cb) {
                    $model->update($attributes);
                    if (isset($cb)) {
                        $cb($model);
                    }
                    return $model;
                });
            }
        );
    }

    public function delete($id, callable $cb = null, $action_log = 'deleted'): Model
    {
        return DB::transaction(
            function () use ($id, $cb, $action_log) {
                return $this->withLogHandler('deleted', ['description' => $this->defaultOption['logname'] . '.' . $action_log, 'id' => $id], function ($model) use ($cb) {
                    if (isset($cb)) {
                        $cb($model);
                    }
                    $model->delete();
                    return $model;
                });
            }
        );
    }
}
