<?php

namespace EasyApiBundle\Util;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use EasyApiBundle\Services\User\UserManager;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Doctrine\Common\Persistence\ObjectManager;

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
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    /**
     * @param $entity
     */
    protected function removeAndFlush($entity)
    {
        $em = $this->getDoctrine()->getManager();
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
     * @return EntityRepository
     */
    protected function getUserRepository()
    {
        return $this->getRepository($this->getUserClassname());
    }

    /**
     * @return UserManager
     */
    protected function getUserManager()
    {
        return $this->container->get('app.user.manager');
    }

    /**
     * @return AdapterInterface
     */
    protected function getCache(): AdapterInterface
    {
        return $this->getContainer()->get('cache.app');
    }

//    /**
//     * @return CoreMailer
//     */
//    protected function getMailer()
//    {
//        return $this->getContainer()->get('api_mailer');
//    }
//
//    /**
//     * @return FileUploader
//     */
//    protected function getMediaUploader()
//    {
//        return $this->getContainer()->get('app.service.media_uploader');
//    }

}
