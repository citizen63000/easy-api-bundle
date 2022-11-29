<?php

namespace EasyApiBundle\Util;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Doctrine\Persistence\ObjectManager;

trait CoreUtilsTrait
{
    /**
     * @return ManagerRegistry
     */
    abstract protected function getDoctrine();

    /**
     * @return ContainerInterface
     */
    abstract protected function getContainer();

    /**
     * @return ObjectManager|object
     * @throws \Exception
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @param string $repository
     *
     * @return EntityRepository
     */
    protected function getRepository(string $repository)
    {
        return $this->getDoctrine()->getRepository($repository);
    }

    /**
     * @param $entity
     *
     * @return mixed
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
     */
    protected function removeAndFlush($entity)
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @return string
     */
    protected function getUserClassname(): string
    {
        return $this->getParameter('easy_api.user_class');
    }

    /**
     * @return AdapterInterface
     * @throws \Exception
     */
    protected function getCache(): AdapterInterface
    {
        return $this->getContainer()->get('cache.app');
    }
}
