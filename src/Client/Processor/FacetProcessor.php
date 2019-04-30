<?php

namespace AdimeoDataSuite\Client\Processor;


use AdimeoDataSuite\Client\AdsClient;
use AdimeoDataSuite\Client\Facet\Facet;
use AdimeoDataSuite\Client\Filter\SearchFilter;

/**
 * Class FacetProcessor
 *
 * @package AdimeoDataSuite\Client\Processor
 */
class FacetProcessor extends AbstractProcessor
{

    /**
     * @var String
     */
    protected $facetName;

    /**
     * @var LinkProcessor[]
     */
    protected $values;

    /**
     * @var bool
     */
    protected $sticky;

    /**
     * @var bool
     */
    protected $hasMoreValues;

    /**
     * @var LinkProcessor
     */
    protected $moreValuesLink;

    /**
     * @var AdsClient
     */
    protected $searchClient;

    /**
     * FacetProcessor constructor.
     * @param AdsClient $searchClient
     */
    public function __construct(AdsClient $searchClient)
    {
        $this->searchClient = $searchClient;
    }


    /**
     * @param \AdimeoDataSuite\Client\Context\SearchContext $context
     * @param mixed $data
     * @return $this
     */
    public function process($data)
    {
        /** @var Facet $facet */
        $facet = $data['facet'];
        $this->facetName = $facet->getName();
        foreach ($data['facetData']['buckets'] as $bucket) {
            $filter = new SearchFilter($facet->getName(), $bucket['key']);
            $active = $this->getSearchClient()->getContext()->hasFilter($filter);
            $newContext = clone $this->getSearchClient()->getContext();
            if ($active) {
                $newContext->removeFilter($filter);
            } else {
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
            $this->values[] = (new LinkProcessor())->process($linkData);
            unset($newContext);
        }

        $this->hasMoreValues = isset($data['facetData']['sum_other_doc_count']) && $data['facetData']['sum_other_doc_count'] > 0;
        if ($this->hasMoreValues()) {
            $linkData = [
                'label' => $data['facetSeeMoreLabel'],
                'attributes' => [
                    'rel' => 'nofollow'
                ],
                'urlParameters' => $this->getSearchClient()->getContext()->getUrlParameters()
            ];
            $linkData['urlParameters'] += [
                'facetOptions' => [$this->getFacetName() . ',size,' . (count($data['facetData']['buckets']) + $data['facetPageSize'])]
            ];
            $this->moreValuesLink = (new LinkProcessor())->process($linkData);
            $this->sticky = $facet->isSticky();
        }

        $this->processed = true;
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
     * @return LinkProcessor[]
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
     * @return LinkProcessor
     */
    public function getMoreValuesLink()
    {
        return $this->moreValuesLink;
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
