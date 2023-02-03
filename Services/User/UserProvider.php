<?php

namespace EasyApiBundle\Services\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
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
     * @param string $username
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUser($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('User with username "%s" does not exist.', $username));
        }

        return $user;
    }

    /**
     * @return UserInterface|null
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', $this->getUserClass(), get_class($user)));
        }

        if (null === $reloadedUser = $this->findUser($user->getUsername())) {
            throw new UsernameNotFoundException(sprintf('User with username "%s" could not be reloaded.', $user->getUsername()));
        }

        return $reloadedUser;
    }

    /**
     * @param string $class
     */
    public function supportsClass($class): bool
    {
        $userClass = $this->getUserClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    protected function findUser(string $username): ?UserInterface
    {
        return $this->entityManager->getRepository($this->getUserClass())->findByUsername($username);
    }
}
