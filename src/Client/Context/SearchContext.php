<?php

namespace AdimeoDataSuite\Client\Context;


use AdimeoDataSuite\Client\Filter\SearchFilter;
use AdimeoDataSuite\Client\Filter\SearchFilterInterface;

/**
 * Class SearchContext
 * @package AdimeoDataSuite\Client\Context
 */
class SearchContext
{

    /**
     * @var string
     */
    protected $query;

    /**
     * @var SearchFilter[]
     */
    protected $filters = [];

    /**
     * @var int
     */
    protected $from;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $sort = '_score';

    /**
     * @var string
     */
    protected $order = 'DESC';

    /**
     * SearchContext constructor.
     * @param string $query
     * @param SearchFilter[] $filters
     * @param int $from
     * @param int $size
     */
    public function __construct($query = '*', array $filters = [], $from = 0, $size = 10)
    {
        $this->query = $query;
        $this->filters = $filters;
        $this->from = $from;
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return SearchFilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param SearchFilterInterface[] $filters
     */
    public function setFilters($filters)
    {
        $this->filters = [];
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param int $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @param SearchFilter $currentFilter
     * @return bool
     */
    public function hasFilter(SearchFilterInterface $filter)
    {
        foreach ($this->getFilters() as $currentFilter) {
            if ($currentFilter->getQuerystringPart() == $filter->getQuerystringPart()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param SearchFilterInterface $filter
     */
    public function addFilter(SearchFilterInterface $filter)
    {
        if (!$this->hasFilter($filter)) {
            $this->filters[] = $filter;
        }
    }

    /**
     * @param SearchFilterInterface $filter
     */
    public function removeFilter(SearchFilterInterface $filter)
    {
        $filters = $this->getFilters();
        for ($i = count($filters) - 1; $i >= 0; $i--) {
            if ($filters[$i]->getQuerystringPart() == $filter->getQuerystringPart()) {
                unset($filters[$i]);
            }
        }
        $this->setFilters(array_values($filters));
    }

    /**
     * @param $field
     * @return int
     */
    public function getFiltersCount($field)
    {
        $count = 0;
        foreach ($this->getFilters() as $filter) {
            if ($filter->getField() == $field) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @param $field
     * @return bool
     */
    public function isColumnSorted($field)
    {
        return $this->getSort() != null && $this->getSort() == $field;
    }

    /**
     * @return array
     */
    public function getUrlParameters()
    {
        $params = [
            'query' => $this->getQuery(),
            'from' => $this->getFrom(),
            'size' => $this->getSize(),
            'filter' => []
        ];
        if ($this->getSort() != null) {
            $params['sort'] = $this->getSort() . ',' . $this->getOrder();
        }
        foreach ($this->getFilters() as $filter) {
            $params['filter'][] = $filter->getQuerystringPart();
        }
        return $params;
    }

}
