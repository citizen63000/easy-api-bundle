<?php

namespace EasyApiBundle\Services\User;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

class UsernameEmailProvider extends UserProvider
{
    /**
     * {@inheritdoc}
     */
    protected function findUser(string $username): ?UserInterface
    {
        /** @var QueryBuilder $qb */
        $qb = $this->entityManager->getRepository($this->getUserClass())->createQueryBuilder('u');

        $or = $qb->expr()->orX();
        $or->add($qb->expr()->eq('u.email', $qb->expr()->literal($username)));
        $or->add($qb->expr()->eq('u.username', $qb->expr()->literal($username)));

        $qb->andWhere($or);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return null;
        }
    }
}
