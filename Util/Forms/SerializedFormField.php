<?php

namespace EasyApiBundle\Util\Forms;

use Symfony\Component\Serializer\Annotation\Groups;

class SerializedFormField
{
    /**
     * @var string
     * @Groups({"public"})
     */
    protected $name;

    /**
     * @var string
     * @Groups({"public"})
     */
    protected $label;

    /**
     * @var string
     * @Groups({"public"})
     */
    protected $placeholder;

    /**
     * @var string
     * @Groups({"public"})
     */
    protected $key;

    /**
     * @var string
     * @Groups({"public"})
     */
    protected $type = '';

    /**
     * @var string
     * @Groups({"public"})
     */
    protected $format;

    /**
     * @var bool
     * @Groups({"public"})
     */
    protected $required;

    /**
     * @var string[]
     * @Groups({"public"})
     */
    protected $conditions = [];

    /**
     * @var string[]
     * @Groups({"private"})
     */
    protected $validationGroups = [];

    /**
     * @var string[]
     * @Groups({"public"})
     */
    protected $conditionedFields = [];

    /**
     * EmbeddedForm.
     *
     * @var SerializedForm
     * @Groups({"public"})
     */
    protected $form;

    /**
     * @var SerializedForm
     * @Groups({"private"})
     */
    protected $parentForm = null;

    /**
     * @var string[]
     * @Groups({"public"})
     */
    protected $values = [];

    /**
     * @var string
     * @Groups({"public"})
     */
    protected $widget = 'input';

    /**
     * @var mixed|null
     * @Groups({"public"})
     */
    protected $defaultValue = '';

    /**
     * @var string
     * @Groups({"public"})
     */
    protected $group = '';

    /**
     * @var string[]
     * @Groups({"public"})
     */
    protected $attr = [];

    /**
     * @var string
     * @Groups({"public"})
     */
    protected $discriminator = '';

    /**
     * @var string[]
     * @Groups({"public"})
     */
    private $dynamicChoices = [];

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
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     */
    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return string
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @param string|null $format
     */
    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required = false): void
    {
        $this->required = $required;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     */
    public function setConditions(array $conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * @return array
     */
    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }

    /**
     * @param array $validationGroups
     */
    public function setValidationGroups(array $validationGroups): void
    {
        $this->validationGroups = $validationGroups;
    }

    /**
     * @return array
     */
    public function getConditionedFields(): array
    {
        return $this->conditionedFields;
    }

    /**
     * @param array $conditionedFields
     */
    public function setConditionedFields(array $conditionedFields): void
    {
        $this->conditionedFields = $conditionedFields;
    }

    /**
     * @return SerializedForm
     */
    public function getForm(): ?SerializedForm
    {
        return $this->form;
    }

    /**
     * @param SerializedForm $form
     */
    public function setForm(SerializedForm $form): void
    {
        $this->form = $form;
    }

    /**
     * @return SerializedForm
     */
    public function getParentForm(): ?SerializedForm
    {
        return $this->parentForm;
    }

    /**
     * @param SerializedForm|null $parentForm
     */
    public function setParentForm(SerializedForm $parentForm = null): void
    {
        $this->parentForm = $parentForm;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    /**
     * @return string|null
     */
    public function getWidget(): ?string
    {
        return $this->widget;
    }

    /**
     * @param string|null $widget
     */
    public function setWidget(?string $widget): void
    {
        $this->widget = $widget;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed|null $defaultValue
     */
    public function setDefaultValue($defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param string|null $group
     */
    public function setGroup(?string $group): void
    {
        $this->group = $group;
    }

    /**
     * @return array
     */
    public function getAttr(): array
    {
        return $this->attr;
    }

    /**
     * @param array $attr
     */
    public function setAttr(array $attr): void
    {
        $this->attr = $attr;
    }

    /**
     * @return string|null
     */
    public function getDiscriminator(): ?string
    {
        return $this->discriminator;
    }

    /**
     * @param string|null $discriminator
     */
    public function setDiscriminator(?string $discriminator): void
    {
        $this->discriminator = $discriminator;
    }

    /**
     * @return array
     */
    public function getDynamicChoices(): array
    {
        return $this->dynamicChoices;
    }

    /**
     * @param array $dynamicChoices
     */
    public function setDynamicChoices(array $dynamicChoices): void
    {
        $this->dynamicChoices = $dynamicChoices;
    }

    /**
     * @return bool
     */
    public function isReferential(): bool
    {
        return 1 === preg_match('/ref[A-Z][a-z]+/', $this->getName());
    }
}
