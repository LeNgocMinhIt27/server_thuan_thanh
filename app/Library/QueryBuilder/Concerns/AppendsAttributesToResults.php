<?php

namespace App\Library\QueryBuilder\Concerns;

use App\Library\QueryBuilder\Exceptions\InvalidAppendQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait AppendsAttributesToResults
{
    /** @var \Illuminate\Support\Collection */
    protected $allowedAppends;

    public function allowedAppends($appends): self
    {
        if ($this->request->appends()->isEmpty()) {
            return $this;
        }
        $appends = is_array($appends) ? $appends : func_get_args();

        $this->allowedAppends = collect($appends);

        $this->ensureAllAppendsExist();

        return $this;
    }

    protected function addAppendsToResults(Collection $results)
    {
        return $results->each(function ($result) {
            if (is_subclass_of($result, Model::class)) {
                $result->append($this->request->appends()->toArray());
            }
            return $result;
        });
    }

    protected function ensureAllAppendsExist()
    {
        $appends = $this->request->appends();

        $diff = $appends->diff($this->allowedAppends);

        if ($diff->count()) {
            throw InvalidAppendQuery::appendsNotAllowed($diff, $this->allowedAppends);
        }
    }
}
