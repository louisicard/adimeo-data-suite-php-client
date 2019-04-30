<?php

namespace AdimeoDataSuite\Client\Render;


use AdimeoDataSuite\Client\SearchContext;
use AdimeoDataSuite\Client\SearchService;

class SearchResultsRenderer extends Renderable
{

  /**
   * @var FacetRender[]
   */
  private $facetRenders = [];

  /**
   * @var ResultRender[]
   */
  private $resultRenders = [];

  /**
   * @var PagerRender
   */
  private $pagerRender;

  /**
   * @var SearchService
   */
  private $searchService;

  private $facetPageSize;

  private $facetSeeMoreLabel;

  /**
   * SearchResultsRenderer constructor.
   * @param SearchService $searchService
   */
  public function __construct($searchService, $facetPageSize = 10, $facetSeeMoreLabel = 'See more')
  {
    $this->searchService = $searchService;
    $this->facetPageSize = $facetPageSize;
    $this->facetSeeMoreLabel = $facetSeeMoreLabel;
  }


  /**
   * @param SearchContext $context
   * @param mixed $results
   * @return self
   */
  public function render($context, $results) {

    if(!$this->isRendered()) {

      if(isset($results['hits']['total']) && $results['hits']['total'] > 0) {
        foreach($results['hits']['hits'] as $hit) {
          $rr = new ResultRender();
          $rr->render($context, $hit);
          $this->addResultRender($rr);
        }
        foreach($this->searchService->getFacets() as $facet) {
          if(isset($results['aggregations'][$facet->getName()]['buckets']) && count($results['aggregations'][$facet->getName()]['buckets']) > 0
            || isset($results['aggregations'][$facet->getName()][$facet->getName()]['buckets']) && count($results['aggregations'][$facet->getName()][$facet->getName()]['buckets']) > 0) {
            $fr = new FacetRender();
            $fr->render($context, [
              'facet' => $facet,
              'facetData' => isset($results['aggregations'][$facet->getName()][$facet->getName()]) ? $results['aggregations'][$facet->getName()][$facet->getName()] : $results['aggregations'][$facet->getName()],
              'facetPageSize' => $this->facetPageSize,
              'facetSeeMoreLabel' => $this->facetSeeMoreLabel,
            ]);
            $this->addFacetRenders($fr);
          }
        }
        if($results['hits']['total'] > $context->getSize()) {
          $pageRender = new PagerRender();
          $pageRender->render($context, $results);
          $this->setPagerRender($pageRender);
        }
      }

      $this->rendered = true;
    }
    return $this;
  }

  /**
   * @param SearchContext $context
   * @param string $field
   * @param string $label
   * @param string $order ASC or DESC
   */
  public function getSortLinkRender($context, $field, $label, $order) {
    $active = $context->getSort() == $field && strtolower($order) == strtolower($context->getOrder());
    $newContext = clone $context;
    $newContext->setOrder($order);
    $newContext->setSort($field);
    $link = new LinkRender();
    $link->render($context, [
      'label' => $label,
      'attributes' => [
        'rel' => 'nofollow',
        'class' => $active ? 'active' : 'inactive'
      ],
      'urlParameters' => $newContext->getUrlParameters()
    ]);
    return $link;
  }

  /**
   * @return FacetRender[]
   */
  public function getFacetRenders()
  {
    return $this->facetRenders;
  }

  /**
   * @param FacetRender $facetRender
   */
  private function addFacetRenders($facetRender)
  {
    $this->facetRenders[] = $facetRender;
  }

  /**
   * @return ResultRender[]
   */
  public function getResultRenders()
  {
    return $this->resultRenders;
  }

  /**
   * @param ResultRender $resultRender
   */
  private function addResultRender($resultRender)
  {
    $this->resultRenders[] = $resultRender;
  }

  /**
   * @return PagerRender
   */
  public function getPagerRender()
  {
    return $this->pagerRender;
  }

  /**
   * @param PagerRender $pagerRender
   */
  private function setPagerRender($pagerRender)
  {
    $this->pagerRender = $pagerRender;
  }

  /**
   * @param LinkRender $link
   * @param SearchService $searchService
   * @return string
   */
  public function renderLinkToHTML($link, $searchService) {
    $uri = $_SERVER['REQUEST_URI'];
    if(strpos($uri, '?') !== false) {
      $uri = substr($uri, 0, strpos($uri, '?'));
    }
    $html = '<a href="' . htmlentities($uri . '?' . $searchService->buildQueryString($link->getUrlParameters())) . '"';
    foreach($link->getAttributes() as $k => $attr) {
      $html .= ' ' . $k . '="' . htmlentities($attr) . '"';
    }
    $html .= '>' . $link->getLabel() . (isset($link->getAttributes()['data-doc-count']) ? ' (' . $link->getAttributes()['data-doc-count'] . ')' : '') . '</a>';
    return $html;
  }


}