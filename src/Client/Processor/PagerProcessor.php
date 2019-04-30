<?php

namespace AdimeoDataSuite\Client\Processor;


use AdimeoDataSuite\Client\AdsClient;

/**
 * Class PagerProcessor
 * @package AdimeoDataSuite\Client\Processor
 */
class PagerProcessor extends AbstractProcessor
{



    /** @var AdsClient */
    protected $searchClient;

    /**
     * @var LinkProcessor[]
     */
    protected $links;

    /**
     * PagerProcessor constructor.
     * @param AdsClient $searchClient
     */
    public function __construct(AdsClient $searchClient)
    {
        $this->searchClient = $searchClient;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function process($data)
    {
        $total = isset($data['hits']['total']) ? $data['hits']['total'] : 0;
        $nbPages = (int)ceil($total / $this->getSearchClient()->getContext()->getSize());
        $current = $this->getSearchClient()->getContext()->getFrom() / $this->getSearchClient()->getContext()->getSize() + 1;
        $pagesIndex = [$current];
        $extraCount = 2 - ($nbPages - $current);
        if ($extraCount < 0) {
            $extraCount = 0;
        }
        for ($i = $current - 1; $i >= 1 && $i >= $current - 2 - $extraCount; $i--) {
            array_unshift($pagesIndex, $i);
        }
        for ($i = $current + 1; count($pagesIndex) < 5 && $i <= $nbPages; $i++) {
            $pagesIndex[] = $i;
        }

        $linkData = [];

        if ($current > 1) {
            $newContext = clone $this->getSearchClient()->getContext();
            $newContext->setFrom(0);
            $linkData[] = [
                'label' => '<<',
                'attributes' => [
                    'class' => 'first'
                ],
                'urlParameters' => $newContext->getUrlParameters()
            ];
            $newContext = clone $this->getSearchClient()->getContext();
            $newContext->setFrom(($current - 2) * $this->getSearchClient()->getContext()->getSize());
            $linkData[] = [
                'label' => '<',
                'attributes' => [
                    'class' => 'prev'
                ],
                'urlParameters' => $newContext->getUrlParameters()
            ];
        }
        foreach ($pagesIndex as $index) {
            $newContext = clone $this->getSearchClient()->getContext();
            $newContext->setFrom(($index - 1) * $this->getSearchClient()->getContext()->getSize());
            $linkData[] = [
                'label' => $index,
                'attributes' => [
                    'class' => $index == $current ? 'current' : 'page'
                ],
                'urlParameters' => $newContext->getUrlParameters()
            ];
        }
        if ($current < $nbPages) {
            $newContext = clone $this->getSearchClient()->getContext();
            $newContext->setFrom($current * $this->getSearchClient()->getContext()->getSize());
            $linkData[] = [
                'label' => '>',
                'attributes' => [
                    'class' => 'next'
                ],
                'urlParameters' => $newContext->getUrlParameters()
            ];
            if ($total < 10000) { //Elasticsearch paging limitation
                $newContext = clone $this->getSearchClient()->getContext();
                $newContext->setFrom(($nbPages - 1) * $this->getSearchClient()->getContext()->getSize());
                $linkData[] = [
                    'label' => '>>',
                    'attributes' => [
                        'class' => 'last'
                    ],
                    'urlParameters' => $newContext->getUrlParameters()
                ];
            }
        }

        foreach ($linkData as $linkDatum) {
            $link = new LinkProcessor();
            $this->links[] = $link->process($linkDatum);
        }

        $this->processed = true;

        return $this;
    }

    /**
     * @return LinkProcessor[]
     */
    public function getLinks()
    {
        return $this->links;
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
