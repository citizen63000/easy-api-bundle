<?php

namespace EasyApiBundle\Util\Forms;

use Symfony\Component\Serializer\Annotation\Groups;

class SerializedForm
{
    public const PARENT_TYPE_FORM = 'FORM';
    public const PARENT_TYPE_COLLECTION = 'COLLECTION';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var string
     * @Groups({"private"})
     *
     */
    private $parentType = null;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param string $route
     */
    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    /**
     * @return mixed
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param mixed $fields
     */
    public function setFields($fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @param SerializedFormField $field
     */
    public function addField(SerializedFormField $field): void
    {
        $field->setParentForm($this);
        $this->fields[] = $field;
    }

    /**
     * @param $fieldName
     *
     * @return SerializedForm
     */
    public function removeField($fieldName): SerializedForm
    {
        foreach ($this->fields as $key => $field) {
            if ($fieldName === $field->getName()) {
                unset($this->fields[$key]);
                sort($this->fields);

                break;
            }
        }

        return $this;
    }

    /**
     * @param $name
     *
     * @return SerializedFormField
     */
    public function getField($name): ?SerializedFormField
    {
        foreach ($this->fields as $field) {
            if ($name === $field->getName()) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getParentType(): ?string
    {
        return $this->parentType;
    }

    /**
     * @param string $parentType
     */
    public function setParentType(string $parentType = null): void
    {
        $this->parentType = $parentType;
    }

    /**
     * @return bool
     */
    public function isInCollection(): bool
    {
        return $this->getParentType() === self::PARENT_TYPE_COLLECTION;
    }

}
