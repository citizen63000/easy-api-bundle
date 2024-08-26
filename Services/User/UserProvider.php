<?php

namespace EasyApiBundle\Services\User;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    protected EntityManagerInterface $entityManager;

    protected string $userClass;

    /**
     * UserProvider constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, string $userClass)
    {
        $this->entityManager = $entityManager;
        $this->userClass = $userClass;
    }

    protected function getUserClass(): ?string
    {
        return $this->userClass;
    }

    /**
     * @deprecated
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        $user = $this->findUser($username);

        if (!$user) {
            throw new UserNotFoundException(sprintf('User with username "%s" does not exist.', $username));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): ?UserInterface
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', $this->getUserClass(), get_class($user)));
        }

        if (null === $reloadedUser = $this->findUser($user->getUsername())) {
            throw new \Exception(sprintf('User with username "%s" could not be reloaded.', $user->getUsername()));
        }

        return $reloadedUser;
    }

    public function supportsClass(string $class): bool
    {
        $userClass = $this->getUserClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    protected function findUser(string $username): ?UserInterface
    {
        return $this->entityManager->getRepository($this->getUserClass())->findOneBy(['username', $username]);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->findUser($identifier);

        if (!$user) {
            throw new \Exception(sprintf('User with identifier "%s" does not exist.', $identifier));
        }

        return $user;
    }
}
