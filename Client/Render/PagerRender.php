<?php

namespace AdimeoDataSuite\Client\Render;


class PagerRender extends Renderable
{
  /**
   * @var LinkRender[]
   */
  private $links;

  public function render($context, $data)
  {
    $total = isset($data['hits']['total']) ? $data['hits']['total'] : 0;
    $nbPages = (int)ceil($total / $context->getSize());
    $current = $context->getFrom() / $context->getSize() + 1;
    $pagesIndex = [$current];
    $extraCount = 2 - ($nbPages - $current);
    if($extraCount < 0)
      $extraCount = 0;
    for($i = $current - 1; $i >= 1 && $i >= $current - 2 - $extraCount; $i--) {
      array_unshift($pagesIndex, $i);
    }
    for($i = $current + 1; count($pagesIndex) < 5 && $i <= $nbPages; $i++) {
      $pagesIndex[] = $i;
    }

    $linkData = [];

    if($current > 1) {
      $newContext = clone $context;
      $newContext->setFrom(0);
      $linkData[] = [
        'label' => '<<',
        'attributes' => [
          'class' => 'first'
        ],
        'urlParameters' => $newContext->getUrlParameters()
      ];
      $newContext = clone $context;
      $newContext->setFrom(($current - 2) * $context->getSize());
      $linkData[] = [
        'label' => '<',
        'attributes' => [
          'class' => 'prev'
        ],
        'urlParameters' => $newContext->getUrlParameters()
      ];
    }
    foreach($pagesIndex as $index) {
      $newContext = clone $context;
      $newContext->setFrom(($index - 1) * $context->getSize());
      $linkData[] = [
        'label' => $index,
        'attributes' => [
          'class' => $index == $current ? 'current' : 'page'
        ],
        'urlParameters' => $newContext->getUrlParameters()
      ];
    }
    if($current < $nbPages) {
      $newContext = clone $context;
      $newContext->setFrom($current * $context->getSize());
      $linkData[] = [
        'label' => '>',
        'attributes' => [
          'class' => 'next'
        ],
        'urlParameters' => $newContext->getUrlParameters()
      ];
      if($total < 10000) { //Elasticsearch paging limitation
        $newContext = clone $context;
        $newContext->setFrom(($nbPages - 1) * $context->getSize());
        $linkData[] = [
          'label' => '>>',
          'attributes' => [
            'class' => 'last'
          ],
          'urlParameters' => $newContext->getUrlParameters()
        ];
      }
    }

    foreach($linkData as $linkDatum) {
      $link = new LinkRender();
      $link->render($context, $linkDatum);
      $this->links[] = $link;
    }

    unset($newContext);

    $this->rendered = true;
    return $this;
  }

  /**
   * @return LinkRender[]
   */
  public function getLinks()
  {
    return $this->links;
  }



}