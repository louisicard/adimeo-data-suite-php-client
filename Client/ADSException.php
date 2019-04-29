<?php

namespace AdimeoDataSuite\Client;


use Throwable;

class ADSException extends \Exception
{
  public function __construct($message = "", $code = 0, Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }


}