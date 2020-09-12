<?php

namespace EasyApiBundle\Validator\Filter;

use Symfony\Component\Validator\Constraint;

class SortConstraint extends Constraint
{
    public const malformedSort = 'sort.malformed';
    public const invalidFieldSort = 'sort.%s.invalid';

    /**
     * @var array|mixed
     */
    protected $fields = [];

    /**
     * SortConstraint constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->fields = $options['fields'] ?? [];
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}