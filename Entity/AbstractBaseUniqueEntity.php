<?php

namespace EasyApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\MappedSuperclass]
abstract class AbstractBaseUniqueEntity extends AbstractBaseEntity
{
    #[ORM\Column(type: 'uuid', nullable: false)]
    #[Groups(['abstract_base_entity_uuid', 'abstract_base_entity', 'abstract_base_entity_full'])]
    protected ?UuidInterface $uuid;

    public function __construct()
    {
        parent::__construct();
        $this->uuid = Uuid::uuid4();
    }

    public function __clone()
    {
        parent::__clone();
        $this->uuid = Uuid::uuid4();
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(?UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }
}
