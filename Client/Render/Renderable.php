<?php

namespace AdimeoDataSuite\Client\Render;


use AdimeoDataSuite\Client\SearchContext;

abstract class Renderable
{

  protected $rendered = false;

  /**
   * @param SearchContext $context
   * @param mixed $data
   */
  abstract function render($context, $data);

  /**
   * @return bool
   */
  public function isRendered() {
    return $this->rendered;
  }

}