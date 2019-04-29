<?php

namespace AdimeoDataSuite\Client;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;

class SearchService
{
  /**
   * @var string
   */
  private $adsUrl;

  /**
   * @var string
   */
  private $mapping;

  /**
   * @var string
   */
  private $analyzer;

  /**
   * @var Client
   */
  private $client;

  /**
   * @var Facet[]
   */
  private $facets;

  private $queryStringParsed = false;

  public function __construct($adsUrl, $mapping, $analyzer = 'standard', $facets = []) {
    $this->adsUrl = $adsUrl;
    $this->mapping = $mapping;
    $this->analyzer = $analyzer;
    $this->facets = $facets;
    $this->client = new Client();
  }

  /**
   * @param SearchContext $context
   * @return mixed
   * @throws ADSException
   */
  public function search($context) {

    $url = $this->buildAPIUrl($context);

    try {
      $response = $this->client->get($url);
    }
    catch(ServerException $ex) {
      $json = json_decode((string)$ex->getResponse()->getBody(), true);
      if(isset($json['error'])) {
        $json = json_decode($json['error'], true);
      }
      throw new ADSException('ADS responded with an error: ' . (isset($json['error']['root_cause'][0]['reason']) ? $json['error']['root_cause'][0]['reason'] : json_encode($json)));
    }

    return json_decode($response->getBody(), true);
  }

  /**
   * @param SearchContext $searchContext
   */
  public function buildAPIUrl($context) {
    $querystringParts = [
      'mapping' => $this->mapping,
      'analyzer' => $this->analyzer
    ];

    $querystringParts += $context->getUrlParameters();

    $facets = [];
    $facetOptions = [];
    $stickyFacets = [];
    foreach($this->getFacets() as $facet) {
      $facets[] = $facet->getName();
      if($facet->isSticky()) {
        $stickyFacets[] = $facet->getName();
      }
      foreach($facet->getOptions() as $option) {
        $facetOptions[] = $facet->getName() . ',' . $option->getOptionType() . ',' . $option->getOptionValue();
      }
    }
    $querystringParts['facets'] = implode(',', $facets);
    $querystringParts['sticky_facets'] = implode(',', $stickyFacets);
    $querystringParts['facetOptions'] = $facetOptions;

    $url = $this->adsUrl . '/search-api/v2?' . $this->buildQueryString($querystringParts);

    return $url;
  }

  public function buildQueryString($params) {
    $urlParts = [];
    foreach($params as $k => $v) {
      if(is_array($v)) {
        foreach($v as $vv) {
          $urlParts[] = urlencode($k . '[]') . '=' . urlencode($vv);
        }
      }
      else {
        $urlParts[] = urlencode($k) . '=' . urlencode($v);
      }
    }

    return implode('&', $urlParts);
  }

  /**
   * @param string $queryString
   * @return SearchContext
   */
  public function parseQueryString($queryString) {
    $parts = explode('&', $queryString);
    $context = new SearchContext();
    $filters = [];
    $params = [];
    foreach($parts as $part) {
      $k = urldecode(substr($part, 0, strpos($part, '=')));
      $v = urldecode(substr($part, strpos($part, '=') + 1));
      preg_match_all('/(?<array>\[[0-9]*\])/i', $k, $matches);
      if(isset($matches['array']) && !empty($matches['array'])) {
        $params[preg_replace('/(?<array>\[[0-9]*\])/i', '', $k)][] = $v;
      }
      else {
        $params[$k] = $v;
      }
    }
    foreach($params as $k => $v) {
      if($k == 'query') {
        $context->setQuery($v);
      }
      elseif($k == 'from') {
        $context->setFrom($v);
      }
      elseif($k == 'size') {
        $context->setSize($v);
      }
      elseif($k == 'sort') {
        $context->setSort(explode(',', $v)[0]);
        $context->setOrder(explode(',', $v)[1]);
      }
      elseif($k == 'filter') {
        foreach($v as $vv) {
          $filters[] = SearchFilter::parse($vv);
        }
      }
      elseif($k == 'facetOptions') {
        foreach($v as $vv) {
          $optionDef = explode(',', $vv);
          $facetName = $optionDef[0];
          $optionType = $optionDef[1];
          $optionValue = substr($vv, strlen($facetName . ',' . $optionType . ','));
          foreach($this->getFacets() as $i => $facet) {
            if($facet->getName() == $facetName) {
              $this->facets[$i]->addOption(new FacetOption($optionType, $optionValue));
            }
          }
        }
      }
    }
    $context->setFilters($filters);

    $this->queryStringParsed = true;

    return $context;
  }

  /**
   * @return Facet[]
   */
  public function getFacets()
  {
    return $this->facets;
  }

  /**
   * @param Facet[] $facets
   */
  public function setFacets($facets)
  {
    if($this->queryStringParsed) {
      throw new ADSException('Query string has already been parsed. Can no longer add facets.');
    }
    $this->facets = $facets;
  }

  /**
   * @param Facet $facet
   */
  public function addFacet($facet) {
    if($this->queryStringParsed) {
      throw new ADSException('Query string has already been parsed. Can no longer add facets.');
    }
    $this->facets[] = $facet;
  }
}