<?php

namespace EasyApiBundle\Entity\User;

use EasyApiBundle\Entity\AbstractBaseUniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use EasyApiCore\Util\ApiProblem;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use \Symfony\Component\Security\Core\User\UserInterface;

#[ORM\MappedSuperclass]
#[UniqueEntity(fields: 'username', message: ApiProblem::USER_USERNAME_ALREADY_EXISTS)]
#[UniqueEntity(fields: 'email', message: ApiProblem::USER_EMAIL_ALREADY_EXISTS)]
abstract class AbstractUser extends AbstractBaseUniqueEntity implements UserInterface
{
    public const ROLE_BASIC_USER = 'ROLE_BASIC_USER';
    public const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    #[ORM\Column(type: 'string')]
    protected ?string $username = null;

    #[ORM\Column(type: 'string')]
    protected ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $enabled = false;

    #[ORM\Column(type: 'array')]
    protected array $roles = [];

    /** Implement it if necessary */
    public function getPassword()
    {
    }

    /** Implement it if necessary */
    public function getSalt()
    {
    }

    /** Implement it if necessary */
    public function eraseCredentials(): void
    {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param string $role
     * @return $this
     */
    public function addRole(string $role): self
    {
        $role = strtoupper($role);
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @param $role
     * @return $this
     */
    public function removeRole($role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled = false): void
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(static::ROLE_ADMIN) || $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    /**
     * {@inheritdoc}
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }
}
