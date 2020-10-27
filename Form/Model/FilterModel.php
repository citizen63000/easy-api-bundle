<?php

namespace EasyApiBundle\Form\Model;

class FilterModel
{
    /** @var array */
    private $defaultSort = [];

    /** @var array */
    private $fields = [];

    /** @var array */
    private $sortFields = [];

    /** @var string */
    private $entityClass;

    /** @var string */
    private $sort;

    /** @var int */
    private $page = 1;

    /** @var int */
    private $limit;

    /**
     * FilterModel constructor.
     * @param array $filters
     */
    public function __construct($filters = [])
    {
        foreach ($filters as $filter) {
            $this->$filter = null;
        }
    }

    /**
     * @param string $property
     * @return null
     */
    public function __get(string $property)
    {
        return $this->$property ?? null;
    }

    /**
     * @param string $property
     * @param $value
     */
    public function __set(string $property, $value): void
    {
        $this->$property = $value;
    }

    /**
     * @return array
     */
    public function getDefaultSort(): ?array
    {
        return $this->defaultSort;
    }

    /**
     * @param array $defaultSort
     */
    public function setDefaultSort(array $defaultSort = null): void
    {
        $this->defaultSort = $defaultSort;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getSortFields(): array
    {
        return $this->sortFields;
    }

    /**
     * @param array $sortFields
     */
    public function setSortFields(array $sortFields): void
    {
        $this->sortFields = $sortFields;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     */
    public function setEntityClass(string $entityClass): void
    {
        $this->entityClass = $entityClass;
    }

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
