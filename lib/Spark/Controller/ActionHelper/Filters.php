<?php

namespace Spark\Controller\ActionHelper;

trait Filters
{
    private $filters = [];

    function beforeFilter($filter, $options = [])
    {
        return $this->addFilter("before", $filter, $options);
    }

    function afterFilter($filter, $options = [])
    {
        return $this->addFilter("after", $filter, $options);
    }

    function onBeforeFilter()
    {
        return $this->dispatchFilters('before');
    }

    function onAfterFilter()
    {
        return $this->dispatchFilters('after');
    }

    private function dispatchFilters($type)
    {
        if (!isset($this->filters[$type])) return;

        foreach ($this->filters[$type] as $filter) {
            list($callback, $options) = $filter;

            $returnValue = $callback($this);

            if ($returnValue instanceof Response) return $returnValue;
        }
    }

    private function addFilter($type, $filter, $options)
    {
        if (!isset($this->filters[$type])) {
            $this->filters[$type] = [];
        }

        if (is_string($filter) and is_callable([$this, $filter])) {
            $callback = [$this, $filter];
        } else if (is_callable($filter)) {
            $callback = $filter;
        }

        $this->filters[$type][] = [$callback, $options];

        return $this;
    }
}
