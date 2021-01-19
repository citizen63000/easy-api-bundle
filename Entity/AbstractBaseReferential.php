<?php

namespace EasyApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Annotation\Groups;

/** @MappedSuperclass */
abstract class AbstractBaseReferential
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"referential_full", "referential_short", "abstract_base_referential_full", "abstract_base_referential_short"})
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     * @Groups({"referential_full", "referential_short", "abstract_base_referential_full", "abstract_base_referential_short"})
     */
    protected $code;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Groups({"referential_full", "referential_short", "abstract_base_referential_full", "abstract_base_referential_short"})
     */
    protected $name;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Groups({"referential_full", "abstract_base_referential_full"})
     */
    protected $rank;

    /**
     * @return string|null
     */
    public function __toString(): ?string
    {
        return $this->getName();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int|null
     */
    public function getRank(): ?int
    {
        return $this->rank;
    }

    /**
     * @param int|null $rank
     */
    public function setRank(?int $rank): void
    {
        $this->rank = $rank;
    }
}
