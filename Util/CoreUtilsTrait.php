<?php

namespace EasyApiBundle\Util;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * @deprecated use EasyApiCore\Util\CoreUtilsTrait instead, will be remove in 4.0
 */
trait CoreUtilsTrait
{
    /**
     * @return ManagerRegistry
     */
    abstract protected function getDoctrine();

    /**
     * @return ObjectManager|object
     * @throws \Exception
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return ObjectRepository
     */
    protected function getRepository(string $repository)
    {
        return $this->getDoctrine()->getRepository($repository);
    }

    /**
     * @param $entity
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function persistAndFlush($entity)
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    /**
     * @param $entity
     *
     * @throws \Exception
     */
    protected function removeAndFlush($entity)
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }

    protected function getUserClassname(): string
    {
        return $this->getParameter('easy_api.user_class');
    }

    /**
     * @throws \Exception
     */
    protected function getCache(): AdapterInterface
    {
        return $this->getContainer()->get('cache.app');
    }
}
