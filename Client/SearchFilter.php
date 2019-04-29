<?php

namespace AdimeoDataSuite\Client;


class SearchFilter
{
  private $field;

  private $value;

  /**
   * SearchFilter constructor.
   * @param $field
   * @param $value
   */
  public function __construct($field, $value)
  {
    $this->field = $field;
    $this->value = $value;
  }


  /**
   * @return string
   */
  public function getField()
  {
    return $this->field;
  }

  /**
   * @param mixed $field
   */
  public function setField($field)
  {
    $this->field = $field;
  }

  /**
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * @param mixed $value
   */
  public function setValue($value)
  {
    $this->value = $value;
  }

  public function getQuerystringPart() {
    return $this->getField()  . '="' . str_replace('"', '\\"', $this->getValue()) . '"';
  }

  public static function parse($urlPart) {
    preg_match_all('/(?<field>[^=]*)="(?<value>.*)"/i', $urlPart, $matches);
    if(isset($matches['field']) && isset($matches['value']) && !empty($matches['field']) && !empty($matches['value'])) {
      return new static($matches['field'][0], $matches['value'][0]);
    }
    return null;
  }
}