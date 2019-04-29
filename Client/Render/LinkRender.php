<?php

namespace AdimeoDataSuite\Client\Render;


class LinkRender extends Renderable
{

  /**
   * @var string
   */
  private $label;

  /**
   * @var array
   */
  private $urlParameters;

  /**
   * @var array
   */
  private $attributes;


  public function render($context, $data)
  {
    if(isset($data['label'])) {
      $this->label = $data['label'];
    }
    if(isset($data['urlParameters'])) {
      $this->urlParameters = $data['urlParameters'];
    }
    if(isset($data['attributes'])) {
      $this->attributes = $data['attributes'];
    }
    $this->rendered = true;
    return $this;
  }

  /**
   * @return string
   */
  public function getLabel()
  {
    return $this->label;
  }

  /**
   * @return array
   */
  public function getUrlParameters()
  {
    return $this->urlParameters;
  }

  /**
   * @return array
   */
  public function getAttributes()
  {
    return $this->attributes;
  }



}