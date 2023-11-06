<?php

namespace App\Repositories;

use App\Traits\UploadImage;

abstract class EloquentRepository implements RepositoryInterface
{
    use UploadImage;
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $_model;
    protected function model()
    {
        return $this->_model;
    }
    public function __construct()
    {
        $this->setBaseModel();
        $this->setDefaultOption(['logname' => 'system', 'description' => '', 'id' => null]);
        $this->init();
    }

    /**
     * get model
     * @return string
     */
    abstract public function getModel();

    public function init()
    {
    }
    public function setBaseModel()
    {
        $this->_model = app()->make(
            $this->getModel()
        );
    }
    public function find($id)
    {
        $result = $this->getModel()::findOrFail($id);
        return $result;
    }
    protected $defaultOption = [];
    public function setDefaultOption($option)
    {
        $this->defaultOption = array_merge($this->defaultOption, $option);
    }
    public function withLogHandler(string $method, $option, $callback, $subject = null, $properties = null)
    {
        // $option = array_merge($this->defaultOption, $option);
        $id = $option['id'];
        if (isset($option['getModel'])) {
            $model = $option['getModel']($id);
        } else {
            if (isset($id)) {
                $model = $this->find($id);
            } else {
                $model = $this->_model;
            }
        }
        // if ($method != 'update')
        //     $old_data = $model->toArray();
        $result = $callback($model);
        // $new_data = $result->toArray();
        // $attrs = ['attributes' => $new_data, 'old' =>  $old_data ?? null];

        // if ((isset($option['disableLog']) && $option['disableLog']) || ($method == 'updated' && ($result->isLogEmpty($attrs) || count($model->getChanges()) == 0) && !$result->shouldSubmitEmptyLogs())) {
        // } else {
        //     $subject = isset($subject) ? $subject : $result;
        //     if (isset($properties)) {
        //         if (is_array($properties)) {
        //             $attrs["other"] = $properties;
        //         } else {
        //             $attrs["other"] = $properties($result);
        //         }
        //     }
        //     if (empty($option['disableLog']))
        //         LogHelper::logOption([
        //             'logname' => $option['logname'],
        //             'method' => $method,
        //             'description' => $option['description'],
        //             'properties' => $attrs,
        //             'subject' => $subject,
        //             'trans_properties' => $option['trans_properties'] ?? null
        //         ]);
        // }
        return $result;
    }
    // public function log($option, $logOption): Activitylog
    // {
    //     $option = array_merge($this->defaultOption, $option, $logOption);
    //     return LogHelper::logOption($option);
    // }
}
