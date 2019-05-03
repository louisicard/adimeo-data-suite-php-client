<?php

namespace AdimeoDataSuite\Client\Processor;


use AdimeoDataSuite\Client\AdsClient;
use AdimeoDataSuite\Client\Context\SearchContext;

/**
 * Class SearchResultsProcessor
 *
 * @package AdimeoDataSuite\Client\Processor
 */
class SearchResultsProcessor extends AbstractProcessor
{

    /**
     * @var FacetProcessor[]
     */
    protected $facetProcessors = [];

    /**
     * @var ResultProcessor[]
     */
    protected $resultProcessors = [];

    /**
     * @var PagerProcessor
     */
    protected $pagerProcessor;

    /**
     * @var AdsClient
     */
    protected $searchClient;

    /**
     * @var int
     */
    protected $facetPageSize;

    /**
     * @var string
     */
    protected $facetSeeMoreLabel;

    /**
     * SearchResultsProcessor constructor.
     * @param $searchClient
     * @param int $facetPageSize
     * @param string $facetSeeMoreLabel
     */
    public function __construct($searchClient, $facetPageSize = 10, $facetSeeMoreLabel = 'See more')
    {
        $this->searchClient = $searchClient;
        $this->facetPageSize = $facetPageSize;
        $this->facetSeeMoreLabel = $facetSeeMoreLabel;
    }


    /**
     * @param SearchContext $context
     * @param mixed $results
     * @return $this
     */
    public function process($results)
    {

        if (!$this->isProcessed()) {

            if (isset($results['hits']['total']) && $results['hits']['total'] > 0) {
                foreach ($results['hits']['hits'] as $hit) {
                    $rr = new ResultProcessor();
                    $rr->process($hit);
                    $this->addResultProcessor($rr);
                }
                foreach ($this->searchClient->getFacets() as $facet) {
                    if (isset($results['aggregations'][$facet->getName()]['buckets']) && count($results['aggregations'][$facet->getName()]['buckets']) > 0
                        || isset($results['aggregations'][$facet->getName()][$facet->getName()]['buckets']) && count($results['aggregations'][$facet->getName()][$facet->getName()]['buckets']) > 0) {
                        $fr = new FacetProcessor($this->getSearchClient());
                        $fr->process([
                            'facet' => $facet,
                            'facetData' => isset($results['aggregations'][$facet->getName()][$facet->getName()]) ? $results['aggregations'][$facet->getName()][$facet->getName()] : $results['aggregations'][$facet->getName()],
                            'facetPageSize' => $this->facetPageSize,
                            'facetSeeMoreLabel' => $this->facetSeeMoreLabel,
                        ]);
                        $this->addFacetProcessor($fr);
                    }
                }
                if ($results['hits']['total'] > $this->searchClient->getContext()->getSize()) {
                    $pageProcessor = new PagerProcessor($this->getSearchClient());
                    $pageProcessor->process($results);
                    $this->setPagerProcessor($pageProcessor);
                }
            }

            $this->processed = true;
        }
        return $this;
    }

    /**
     * @param SearchContext $context
     * @param string $field
     * @param string $label
     * @param string $order ASC or DESC
     */
    public function getSortLinkProcessor($field, $label, $order)
    {
        $active = $this->searchClient->getContext()->getSort() == $field && strtolower($order) == strtolower($this->searchClient->getContext()->getOrder());
        $newContext = clone $this->searchClient->getContext();
        $newContext->setOrder($order);
        $newContext->setSort($field);
        $link = new LinkProcessor();
        $link->process([
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
     * @return FacetProcessor[]
     */
    public function getFacetProcessors()
    {
        return $this->facetProcessors;
    }

    /**
     * @param FacetProcessor $facetProcessor
     */
    protected function addFacetProcessor($facetProcessor)
    {
        $this->facetProcessors[] = $facetProcessor;
    }

    /**
     * @return ResultProcessor[]
     */
    public function getResultProcessors()
    {
        return $this->resultProcessors;
    }

    /**
     * @param ResultProcessor $resultProcessor
     */
    protected function addResultProcessor($resultProcessor)
    {
        $this->resultProcessors[] = $resultProcessor;
    }

    /**
     * @return PagerProcessor
     */
    public function getPagerProcessor()
    {
        return $this->pagerProcessor;
    }

    /**
     * @param PagerProcessor $pagerProcessor
     */
    protected function setPagerProcessor($pagerProcessor)
    {
        $this->pagerProcessor = $pagerProcessor;
        return $this;
    }

    /**
     * @param LinkProcessor $link
     * @param AdsClient $searchService
     * @return string
     */
    public function renderLinkToHTML($link, $searchService)
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        $html = '<a href="' . htmlentities($uri . '?' . $searchService->buildQueryString($link->getUrlParameters())) . '"';
        foreach ($link->getAttributes() as $k => $attr) {
            $html .= ' ' . $k . '="' . htmlentities($attr) . '"';
        }
        $html .= '>' . $link->getLabel() . (isset($link->getAttributes()['data-doc-count']) ? ' (' . $link->getAttributes()['data-doc-count'] . ')' : '') . '</a>';
        return $html;
    }

    /**
     * @return AdsClient
     */
    public function getSearchClient()
    {
        return $this->searchClient;
    }

    /**
     * @param AdsClient $searchClient
     */
    public function setSearchClient($searchClient)
    {
        $this->searchClient = $searchClient;
        return $this;
    }

}
