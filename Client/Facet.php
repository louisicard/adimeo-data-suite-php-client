<?php

namespace AdimeoDataSuite\Client;


class Facet
{

  /**
   * @var string
   */
  private $name;

  /**
   * @var bool
   */
  private $sticky;

  /**
   * @var FacetOption[]
   */
  private $options;

  /**
   * Facet constructor.
   * @param string $name
   */
  public function __construct($name)
  {
    $this->name = $name;
    $this->sticky = false;
    $this->options = [];
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @return bool
   */
  public function isSticky()
  {
    return $this->sticky;
  }

  /**
   * @param bool $sticky
   */
  public function setSticky($sticky)
  {
    $this->sticky = $sticky;
  }

  /**
   * @return FacetOption[]
   */
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * @param FacetOption $option
   */
  public function addOption($option) {
    $this->options[$option->getOptionType()] = $option;
  }

}