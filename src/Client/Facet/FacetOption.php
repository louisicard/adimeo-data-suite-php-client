<?php

namespace AdimeoDataSuite\Client\Facet;


class FacetOption implements FacetOptionInterface
{
    /**
     * @var string
     */
    protected $optionType;

    /**
     * @var string
     */
    protected $optionValue;

    /**
     * FacetOption constructor.
     * @param string $optionType
     * @param string $optionValue
     */
    public function __construct($optionType, $optionValue)
    {
        $this->optionType = $optionType;
        $this->optionValue = $optionValue;
    }

    /**
     * @return string
     */
    public function getOptionType()
    {
        return $this->optionType;
    }

    /**
     * @param string $optionType
     */
    public function setOptionType($optionType)
    {
        $this->optionType = $optionType;
        return $this;
    }

    /**
     * @return string
     */
    public function getOptionValue()
    {
        return $this->optionValue;
    }

    /**
     * @param string $optionValue
     */
    public function setOptionValue($optionValue)
    {
        $this->optionValue = $optionValue;
        return $this;
    }


}
