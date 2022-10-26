<?php

namespace EasyApiBundle\Util\Tests;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use EasyApiBundle\Util\CoreUtilsTrait;
use Symfony\Component\DependencyInjection\Container;

trait TestUtilsTrait
{
    use CoreUtilsTrait;

    /**
     * @return ManagerRegistry|null
     */
    protected function getDoctrine()
    {
        try {
            return $this->getContainer()->get('doctrine');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $repository
     *
     * @return ObjectRepository
     */
    protected function getRepository(string $repository)
    {
        return self::$entityManager->getRepository($repository);
    }

    /**
     * @param string $id
     * @param int $invalidBehavior
     *
     * @return object
     * @throws \Exception
     */
    protected function get(string $id, int $invalidBehavior = Container::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->getContainer()->get($id, $invalidBehavior);
    }
}
