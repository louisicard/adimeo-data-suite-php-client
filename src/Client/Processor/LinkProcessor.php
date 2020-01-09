<?php

namespace AdimeoDataSuite\Client\Processor;


/**
 * Class LinkProcessor
 *
 * @package AdimeoDataSuite\Client\Processor
 */
class LinkProcessor extends AbstractProcessor
{

    /**
     * @var string
     */
    protected $label;

    /**
     * @var array
     */
    protected $urlParameters;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @param mixed $data
     *
     * @return $this
     */
    public function process($data)
    {
        if (isset($data['label'])) {
            $this->label = $data['label'];
        }
        if (isset($data['urlParameters'])) {
            $this->urlParameters = $data['urlParameters'];
        }
        if (isset($data['attributes'])) {
            $this->attributes = $data['attributes'];
        }
        $this->processed = true;
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
