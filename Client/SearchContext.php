<?php

namespace AdimeoDataSuite\Client;


class SearchContext
{

  /**
   * @var string
   */
  private $query;

  /**
   * @var SearchFilter[]
   */
  private $filters;

  /**
   * @var int
   */
  private $from;

  /**
   * @var int
   */
  private $size;

  /**
   * @var string
   */
  private $sort;

  /**
   * @var string
   */
  private $order = 'ASC';

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
   * @return SearchFilter[]
   */
  public function getFilters()
  {
    return $this->filters;
  }

  /**
   * @param SearchFilter[] $filters
   */
  public function setFilters($filters)
  {
    $this->filters = $filters;
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
   * @param SearchFilter $filter
   * @return bool
   */
  public function hasFilter($filter) {
    foreach($this->getFilters() as $f) {
      if($f->getQuerystringPart() == $filter->getQuerystringPart()) {
        return true;
      }
    }
    return false;
  }

  /**
   * @param SearchFilter $filter
   */
  public function addFilter($filter) {
    if(!$this->hasFilter($filter)) {
      $this->filters[] = $filter;
    }
  }

  /**
   * @param SearchFilter $filter
   */
  public function removeFilter($filter) {
    $filters = $this->getFilters();
    for($i = count($filters) - 1; $i >= 0; $i--) {
      if($filters[$i]->getQuerystringPart() == $filter->getQuerystringPart()) {
        unset($filters[$i]);
      }
    }
    $this->setFilters(array_values($filters));
  }

  public function getFiltersCount($field) {
    $count = 0;
    foreach($this->getFilters() as $filter) {
      if($filter->getField() == $field)
        $count++;
    }
    return $count;
  }

  public function isColumnSorted($field) {
    return $this->getSort() != null && $this->getSort() == $field;
  }

  public function getUrlParameters() {
    $params = [
      'query' => $this->getQuery(),
      'from' => $this->getFrom(),
      'size' => $this->getSize(),
      'filter' => []
    ];
    if($this->getSort() != null) {
      $params['sort'] = $this->getSort() . ',' . $this->getOrder();
    }
    foreach($this->getFilters() as $filter) {
      $params['filter'][] = $filter->getQuerystringPart();
    }
    return $params;
  }

}