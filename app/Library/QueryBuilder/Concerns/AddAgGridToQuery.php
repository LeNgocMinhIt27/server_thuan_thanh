<?php

namespace App\Library\QueryBuilder\Concerns;

use App\Helpers\AggridHelper;

trait AddAgGridToQuery
{
    protected $aggird_filter = [];
    public function allowedAgGrid($aggird_filter = [], $aggird_sort = [])
    {
        $this->is_use_aggird = true;
        $this->aggird_filter = array_merge([
            'date' => function () {
                AggridHelper::convertDateFilterType(...func_get_args());
            },
            'datetime' => function () {
                AggridHelper::convertDateTimeFilterType(...func_get_args());
            },
            'relationship' => function ($filter, $query, $key, $where) {
                $value = $filter['filter'] ?? null;
                if (!empty($value)) {
                    $query->{$where . 'Has'}($filter['relationship'],  function ($query) use ($filter, $value) {
                        $relationship_field = $filter['relationship_field'] ?? 'id';
                        if (!empty($filter['relationship_table'])) {
                            $relationship_field = $filter['relationship_table'] . '.' . $relationship_field;
                        }
                        $query->where($relationship_field, $value);
                    });
                }
            }
        ], $aggird_filter);
        $this->addFunctionToCallWhenGet(
            function () use ($aggird_sort) {
                $params = $this->request->aggrid();
                $sortModel = $params['sortModel'];
                $filterModel = $params['filterModel'];
                if (isset($sortModel) && count($sortModel) > 0) {
                    foreach ($sortModel as $sort) {
                        if (isset($aggird_sort[$sort['colId']])) {
                            $aggird_sort[$sort['colId']]($this, $sort);
                        } else {
                            $this->orderBy($sort['colId'], $sort['sort']);
                        }
                    }
                }
                if (!empty($filterModel))
                    foreach ($filterModel as $key => $filter) {
                        if (isset($filter['operator'])) {
                            $filter['operator'] = strtolower($filter['operator']);
                            $this->where(function ($query) use ($filter, $key) {
                                $condition1 = $filter['condition1'];
                                $this->convertFilterType($condition1, $query, $key);
                                $condition2 = $filter['condition2'];
                                $this->convertFilterType($condition2, $query, $key, $filter['operator'] == 'and' ? 'where' : 'orWhere');
                            });
                        } else {
                            $this->convertFilterType($filter, $this, $key);
                        }
                    }
                return $this;
            }
        );
        return $this;
    }
    public function convertFilterType($filter, $query, $key, $where = 'where')
    {
        $handle = $this->aggird_filter[$filter['filterType']] ?? null;
        if (empty($handle)) {
            AggridHelper::convertTextFilterType($filter, $query, $key, $where);
        } else {
            $handle($filter, $query, $key, $where);
        }
    }
}
