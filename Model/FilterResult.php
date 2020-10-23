<?php

namespace EasyApiBundle\Model;

class FilterResult
{
    /** @var array  */
    protected $results = [];

    /** @var int  */
    protected $nbResults = 0;

    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @param array $results
     */
    public function setResults(array $results): void
    {
        $this->results = $results;
    }

    /**
     * @return int
     */
    public function getNbResults(): int
    {
        return $this->nbResults;
    }

    /**
     * @param int $nbResults
     */
    public function setNbResults(int $nbResults = 0): void
    {
        $this->nbResults = $nbResults;
    }
}