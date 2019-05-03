<?php

namespace AdimeoDataSuite\Client\Facet;


class Facet
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $sticky;

    /**
     * @var FacetOption[]
     */
    protected $options;

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
        return $this;
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
    public function setSticky($sticky = true)
    {
        $this->sticky = (bool)$sticky;

        return $this;
    }

    /**
     * @return FacetOptionInterface[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param FacetOptionInterface $option
     */
    public function addOption(FacetOptionInterface $option)
    {
        $this->options[$option->getOptionType()] = $option;
        return $this;
    }

}
