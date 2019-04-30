<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 2019-04-30
 * Time: 12:05
 */

namespace AdimeoDataSuite\Client\Filter;


/**
 * Class SearchFilter
 * @package AdimeoDataSuite\Client\Filter
 */
interface SearchFilterInterface
{
    /**
     * @param $urlPart
     * @return SearchFilter|null
     */
    public static function parse($urlPart);

    /**
     * @return string
     */
    public function getField();

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return string
     */
    public function getQuerystringPart();
}
