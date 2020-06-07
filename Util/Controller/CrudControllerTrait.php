<?php


namespace EasyApiBundle\Util\Controller;


use EasyApiBundle\Entity\AbstractBaseEntity;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

/**
 * Trait CrudControllerTrait
 * @package EasyApiBundle\Util\Controller
 */
trait CrudControllerTrait
{
    /**
     * @var string
     */
    protected static $entityClass;

    /**
     * @var string
     */
    protected static $entityTypeClass;

    /**
     * CrudControllerTrait constructor.
     */
    public function __construct()
    {
        if (null == static::$entityClass) {
            throw new \Exception('$entityClass can not be null.');
        }

        if (null === static::$entityTypeClass) {
            var_dump(static::$entityClass);
        }
    }

    public function getAction(AbstractBaseEntity $entity)
    {
        return parent::getEntityAction($entity);
    }

    /**
     * @return mixed
     */
    public function getListAction()
    {
        return static::getEntityListAction(static::$entityClass);
    }

    /**
     * @param Request $request
     * @return View|null
     */
    public function createAction(Request $request): ?View
    {
        return static::createEntityAction($request);
    }

    /**
     * @param Request $request
     * @param AbstractBaseEntity $entity
     * @return mixed
     */
    public function updateAction(Request $request, AbstractBaseEntity $entity)
    {
        return static::updateEntityAction($request, $entity);
    }

    /**
     * @param AbstractBaseEntity $entity
     * @return mixed
     */
    public function deleteAction(AbstractBaseEntity $entity)
    {
        return static::deleteEntityAction($entity);
    }

}