<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class HideGeometryScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $query_table = $builder->getModel()->getTable();
        $builder->select(array_map(function ($field) use ($query_table) {
            return $query_table . '.' . $field;
        }, array_merge(['id'], array_diff($model->getFillable(), ['geometry']))));
    }
}
