<?php

namespace EasyApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Annotation\Groups;

/** @MappedSuperclass */
abstract class AbstractBaseReferential
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     * @Groups({"referential_full", "referential_short", "abstract_base_referential_full", "abstract_base_referential_short"})
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     * @Groups({"referential_full", "referential_short", "abstract_base_referential_full", "abstract_base_referential_short"})
     */
    protected ?string $code;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"referential_full", "referential_short", "abstract_base_referential_full", "abstract_base_referential_short"})
     */
    protected ?string $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"referential_full", "abstract_base_referential_full"})
     */
    protected ?int $rank;

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): void
    {
        $this->rank = $rank;
    }
}
