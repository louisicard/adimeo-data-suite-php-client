<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 2019-04-30
 * Time: 12:04
 */

namespace AdimeoDataSuite\Client\Facet;

interface FacetOptionInterface
{
    /**
     * @return string
     */
    public function getOptionType();

    /**
     * @return string
     */
    public function getOptionValue();
}
