<?php

namespace AdimeoDataSuite\Client\Processor;


/**
 * Class AbstractProcessor
 * @package AdimeoDataSuite\Client\Processor
 */
abstract class AbstractProcessor implements ProcessorInterface
{

    /**
     * @var bool
     */
    protected $processed = false;

    /**
     * @return bool
     */
    public function isProcessed()
    {
        return $this->processed;
    }

}
