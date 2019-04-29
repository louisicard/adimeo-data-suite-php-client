<?php

namespace AdimeoDataSuite\Client\Render;


use AdimeoDataSuite\Client\Facet;
use AdimeoDataSuite\Client\SearchFilter;

class FacetRender extends Renderable
{

  /**
   * @var String
   */
  private $facetName;

  /**
   * @var LinkRender[]
   */
  private $values;

  /**
   * @var bool
   */
  private $sticky;

  /**
   * @var bool
   */
  private $hasMoreValues;

  /**
   * @var LinkRender
   */
  private $moreValuesLink;

  public function render($context, $data)
  {
    /** @var Facet $facet */
    $facet = $data['facet'];
    $this->facetName = $facet->getName();
    foreach($data['facetData']['buckets'] as $bucket) {
      $filter = new SearchFilter($facet->getName(), $bucket['key']);
      $active = $context->hasFilter($filter);
      $newContext = clone $context;
      if($active) {
        $newContext->removeFilter($filter);
      }
      else {
        $newContext->addFilter($filter);
      }
      $newContext->setFrom(0);
      $linkData = [
        'label' => $bucket['key'],
        'attributes' => [
          'class' => $active ? 'active' : 'inactive',
          'data-doc-count' => isset($bucket['parent_count']['doc_count']) ? $bucket['parent_count']['doc_count'] : $bucket['doc_count'],
          'rel' => 'nofollow'
        ],
        'urlParameters' => $newContext->getUrlParameters()
      ];
      $this->values[] = (new LinkRender())->render($context, $linkData);
      unset($newContext);
    }

    $this->hasMoreValues = isset($data['facetData']['sum_other_doc_count']) && $data['facetData']['sum_other_doc_count'] > 0;
    if($this->hasMoreValues()) {
      $linkData = [
        'label' => $data['facetSeeMoreLabel'],
        'attributes' => [
          'rel' => 'nofollow'
        ],
        'urlParameters' => $context->getUrlParameters()
      ];
      $linkData['urlParameters'] += [
        'facetOptions' => [$this->getFacetName() . ',size,' . (count($data['facetData']['buckets']) + $data['facetPageSize'])]
      ];
      $this->moreValuesLink = (new LinkRender())->render($context, $linkData);
      $this->sticky = $facet->isSticky();
    }

    $this->rendered = true;
    return $this;
  }

  /**
   * @return String
   */
  public function getFacetName()
  {
    return $this->facetName;
  }

  /**
   * @return LinkRender[]
   */
  public function getValues()
  {
    return $this->values;
  }

  /**
   * @return bool
   */
  public function isSticky()
  {
    return $this->sticky;
  }

  /**
   * @return bool
   */
  public function hasMoreValues()
  {
    return $this->hasMoreValues;
  }

  /**
   * @return LinkRender
   */
  public function getMoreValuesLink()
  {
    return $this->moreValuesLink;
  }


}