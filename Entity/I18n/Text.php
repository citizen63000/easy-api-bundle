<?php

namespace EasyApiBundle\Entity\I18n;

/**
 * Text.
 */
class Text
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $value;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set environment.
     *
     * @param string $environment
     *
     * @return Text
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Get environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return Text
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return Text
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Translation code.
     *
     * @return string
     */
    public function getTranslationCode()
    {
        return strtolower($this->environment).$this->code;
    }
}
