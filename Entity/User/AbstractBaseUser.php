<?php

namespace EasyApiBundle\Entity\User;

use EasyApiBundle\Entity\AbstractBaseUniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use EasyApiBundle\Entity\MagicGettersSettersTrait;

/**
 * @ORM\MappedSuperclass
 * @UniqueEntity(fields="username", message=EasyApiBundle\Util\ApiProblem::USER_USERNAME_ALREADY_EXISTS)
 * @UniqueEntity(fields="email", message=EasyApiBundle\Util\ApiProblem::USER_EMAIL_ALREADY_EXISTS)
 */
abstract class AbstractBaseUser extends AbstractBaseUniqueEntity implements UserInterface
{
    const ROLE_BASIC_USER = 'ROLE_BASIC_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    const ROLE_DEFAULT = 'ROLE_USER';

    use MagicGettersSettersTrait;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $anonymous = false;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date")
     */
    protected $lastLogin;

    /**
     * @var array
     * @ORM\Column(type="string")
     */
    protected $roles;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->enabled = false;
        $this->roles = [];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUsername();
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
}