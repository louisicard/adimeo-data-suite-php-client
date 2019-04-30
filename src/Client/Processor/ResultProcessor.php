<?php

namespace AdimeoDataSuite\Client\Processor;


/**
 * Class ResultProcessor
 * @package AdimeoDataSuite\Client\Processor
 */
class ResultProcessor extends AbstractProcessor
{

    /**
     * @var
     */
    protected $id;

    /**
     * @var
     */
    protected $score;

    /**
     * @var
     */
    protected $source;

    /**
     * @param \AdimeoDataSuite\Client\Context\SearchContext $context
     * @param mixed $data
     * @return $this
     */
    public function process($data)
    {
        $this->id = $data['_id'];
        $this->score = $data['_score'];
        $this->source = $data['_source'];
        $this->processed = true;

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
