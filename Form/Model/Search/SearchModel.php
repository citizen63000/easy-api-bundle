<?php

namespace EasyApiBundle\Form\Model\Search;

/**
 * @deprecated
 */
class SearchModel
{
    /**
     * @var string
     */
    private $sort;

    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var int
     */
    private $limit;

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     */
    public function setSort($sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int|null $page
     */
    public function setPage(int $page = null): void
    {
        if (null !== $page) {
            $this->page = $page;
        }
    }

    /**
     * @return int
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     */
    public function setLimit(int $limit = null): void
    {
        if (null !== $limit) {
            $this->limit = $limit;
        }
    }

    /**
     * @return int
     */
    public function getFirstResult()
    {
        return ($this->getPage() - 1) * (int) $this->getLimit();
    }
}
