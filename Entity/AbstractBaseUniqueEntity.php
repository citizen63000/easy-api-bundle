<?php

namespace EasyApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractBaseUniqueEntity extends AbstractBaseEntity
{
    /**
     * @var UuidInterface
     * @ORM\Column(type="uuid")
     */
    protected $uuid;

    /**
     * AbstractBaseUniqueEntity constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->uuid = Uuid::uuid4();
    }

    /**
     * AbstractBaseUniqueEntity clone.
     */
    public function __clone()
    {
        parent::__clone();
        $this->uuid = Uuid::uuid4();
    }

    /**
     * @return Uuid|null
     */
    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @param UuidInterface|null $uuid
     */
    public function setUuid(?UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }
}
