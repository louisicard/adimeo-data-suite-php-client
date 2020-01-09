<?php

namespace AdimeoDataSuite\Client;


use AdimeoDataSuite\Client\Context\SearchContext;
use AdimeoDataSuite\Client\Facet\Facet;
use AdimeoDataSuite\Client\Facet\FacetOption;
use AdimeoDataSuite\Client\Filter\SearchFilter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;

/**
 * Class AdsClient
 * @package AdimeoDataSuite\Client
 */
class AdsClient
{
    /**
     * @var string
     */
    protected $adsUrl;

    /**
     * @var string
     */
    protected $mapping;

    /**
     * @var string
     */
    protected $analyzer;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Facet[]
     */
    protected $facets = [];

    /**
     * @var bool
     */
    protected $queryStringParsed = false;

    /**
     * @var SearchContext
     */
    protected $context;

    protected $excludedFields = [];

    protected $includedFields = [];


    /**
     * AdsClient constructor.
     * @param $adsUrl
     * @param $mapping
     * @param string $analyzer
     * @param array $facets
     */
    public function __construct($adsUrl, $mapping, $analyzer = 'standard', array $facets = [], $excludedFields = [], $includedFields = [])
    {
        $this->adsUrl = $adsUrl;
        $this->mapping = $mapping;
        $this->analyzer = $analyzer;
        $this->facets = $facets;
        $this->excludedFields = $excludedFields;
        $this->includedFields = $includedFields;
        $this->client = new Client();
    }

    /**
     * @param SearchContext $context
     * @return mixed
     * @throws SearchException
     */
    public function search(SearchContext $context = null)
    {

        if(is_null($context)) {
            $context = $this->getContext();
        }

        $url = $this->buildAPIUrl($context);

        try {
            $response = $this->client->get($url);
        } catch (ServerException $e) {
            $json = json_decode((string)$e->getResponse()->getBody(), true);
            if (isset($json['error'])) {
                $json = json_decode($json['error'], true);
            }
            throw new SearchException('ADS responded with an error: ' . (isset($json['error']['root_cause'][0]['reason']) ? $json['error']['root_cause'][0]['reason'] : json_encode($json)));
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * @param SearchContext $context
     *
     * @return string
     */
    public function buildAPIUrl(SearchContext $context)
    {
        $querystringParts = [
            'mapping' => $this->mapping,
            'analyzer' => $this->analyzer
        ];

        $querystringParts += $context->getUrlParameters();

        $facets = [];
        $facetOptions = [];
        $stickyFacets = [];
        foreach ($this->getFacets() as $facet) {
            $facets[] = $facet->getName();
            if ($facet->isSticky()) {
                $stickyFacets[] = $facet->getName();
            }
            foreach ($facet->getOptions() as $option) {
                $facetOptions[] = $facet->getName() . ',' . $option->getOptionType() . ',' . $option->getOptionValue();
            }
        }
        $querystringParts['facets'] = implode(',', $facets);
        $querystringParts['sticky_facets'] = implode(',', $stickyFacets);
        $querystringParts['facetOptions'] = $facetOptions;

        if(count($this->excludedFields) > 0)
            $querystringParts['exclude_fields'] = implode(',', $this->excludedFields);
        if(count($this->includedFields) > 0)
            $querystringParts['include_fields'] = implode(',', $this->includedFields);


        return $this->adsUrl . '/search-api/v2?' . $this->buildQueryString($querystringParts);
    }

    /**
     * @param $params
     *
     * @return string
     */
    public function buildQueryString($params)
    {
        $urlParts = [];
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $vv) {
                    $urlParts[] = urlencode($k . '[]') . '=' . urlencode($vv);
                }
            } else {
                $urlParts[] = urlencode($k) . '=' . urlencode($v);
            }
        }

        return implode('&', $urlParts);
    }

    /**
     * @param string $queryString
     * @return SearchContext
     */
    public function initContextFromQueryString($queryString)
    {
        $parts = explode('&', $queryString);
        $context = new SearchContext();
        $filters = [];
        $params = [];
        foreach ($parts as $part) {
            $key = urldecode(substr($part, 0, strpos($part, '=')));
            $value = urldecode(substr($part, strpos($part, '=') + 1));
            preg_match_all('/(?<array>\[[0-9]*\])/i', $key, $matches);
            if (isset($matches['array']) && !empty($matches['array'])) {
                $params[preg_replace('/(?<array>\[[0-9]*\])/i', '', $key)][] = $value;
            } else {
                $params[$key] = $value;
            }
        }
        foreach ($params as $key => $value) {
            if ($key == 'query') {
                $context->setQuery($value);
            } elseif ($key == 'from') {
                $context->setFrom($value);
            } elseif ($key == 'size') {
                $context->setSize($value);
            } elseif ($key == 'sort') {
                if(count(explode(',', $value)) == 2) {
                    $context->setSort(explode(',', $value)[0]);
                    $context->setOrder(explode(',', $value)[1]);
                }
                elseif(count(explode(',', $value)) == 5) {
                    $sortR = explode(',', $value);
                    $order = array_pop($sortR);
                    $context->setSort(implode(',', $sortR));
                    $context->setOrder($order);
                }
            } elseif ($key == 'filter') {
                foreach ($value as $filter) {
                    $filters[] = SearchFilter::parse($filter);
                }
            } elseif ($key == 'facetOptions') {
                foreach ($value as $facetOption) {
                    $optionDef = explode(',', $facetOption);
                    $facetName = $optionDef[0];
                    $optionType = $optionDef[1];
                    $optionValue = substr($facetOption, strlen($facetName . ',' . $optionType . ','));
                    foreach ($this->getFacets() as $i => $facet) {
                        if ($facet->getName() == $facetName) {
                            $this->facets[$i]->addOption(new FacetOption($optionType, $optionValue));
                        }
                    }
                }
            }
        }
        $context->setFilters($filters);

        $this->queryStringParsed = true;

        $this->context = $context;

        return $this->context;
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
     *
     * @return AdsClient
     *
     * @throws SearchException
     */
    public function setFacets(array $facets)
    {
        $this->facets = [];
        foreach ($facets as $facet) {
            $this->addFacet($facet);
        }

        return $this;
    }

    /**
     * @param Facet $facet
     *
     * @return AdsClient
     *
     * @throws SearchException
     */
    public function addFacet($facet)
    {
        if ($this->queryStringParsed) {
            throw new SearchException('Query string has already been parsed. Can no longer add facets.');
        }
        $this->facets[] = $facet;

        return $this;
    }

    /**
     * @return SearchContext
     */
    public function getContext()
    {
        if(empty($this->context)) {
            $queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
            $this->initContextFromQueryString($queryString);
        }

        return $this->context;
    }

    /**
     * @param SearchContext $context
     *
     * @return AdsClient
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludedFields()
    {
        return $this->excludedFields;
    }

    /**
     * @param array $excludedFields
     *
     * @return AdsClient
     */
    public function setExcludedFields($excludedFields)
    {
        $this->excludedFields = $excludedFields;

        return $this;
    }

    /**
     * @return array
     */
    public function getIncludedFields()
    {
        return $this->includedFields;
    }

    /**
     * @param array $includedFields
     *
     * @return AdsClient
     */
    public function setIncludedFields($includedFields)
    {
        $this->includedFields = $includedFields;

        return $this;
    }
}
