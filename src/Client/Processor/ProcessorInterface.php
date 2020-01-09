<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 2019-04-30
 * Time: 11:54
 */

namespace AdimeoDataSuite\Client\Processor;

use AdimeoDataSuite\Client\Context\SearchContext;

/**
 * Interface ProcessorInterface
 *
 * @package AdimeoDataSuite\Client\Processor
 */
interface ProcessorInterface
{
    /**
     * @param mixed $data
     */
    function process($data);

    /**
     * @return bool
     */
    public function isProcessed();
}
