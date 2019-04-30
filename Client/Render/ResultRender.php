<?php

namespace AdimeoDataSuite\Client\Render;


class ResultRender extends Renderable
{

  private $id;

  private $score;

  private $source;

  public function render($context, $data)
  {
    $this->id = $data['_id'];
    $this->score = $data['_score'];
    $this->source = $data['_source'];
    $this->rendered = true;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return mixed
   */
  public function getSource()
  {
    return $this->source;
  }

  /**
   * @return mixed
   */
  public function getScore()
  {
    return $this->score;
  }

}