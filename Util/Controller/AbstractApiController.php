<?php

namespace EasyApiBundle\Util\Controller;

use EasyApiBundle\Util\Forms\FormDescriber;
use EasyApiBundle\Util\Forms\FormSerializer;
use EasyApiBundle\Util\Forms\SerializedForm;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Model\User;
use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Form\Model\Search\SearchModel;
use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\CoreUtilsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;

abstract class AbstractApiController extends FOSRestController
{
    use CoreUtilsTrait;

    /**
     * @var string
     */
    protected static $entityClass;

    /**
     * @var string
     */
    protected static $entityTypeClass;

    /**
     * @var string
     */
    protected static $entitySearchModelClass;

    /**
     * @var string
     */
    protected static $entitySearchTypeClass;

    /**
     * @var array
     */
    protected static $serializationGroups;

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return User
     */
    protected function getUser() :?User
    {
        return parent::getUser();
    }

    /**
     * @param $entity
     *
     * @return mixed
     */
    protected function getEntityAction($entity)
    {
        return $entity;
    }

    /**
     * @param string $entityClass
     *
     * @return object[]
     */
    protected function getEntityListAction(string $entityClass = null)
    {
        $entityClass = $entityClass ?? static::$entityClass;

        return $this->getDoctrine()->getRepository($entityClass)->findAll();
    }

    /**
     * @param string $entityClass
     *
     * @return object[]
     */
    protected function getEntityListOrderedAction(string $entityClass = null)
    {
        $entityClass = $entityClass ?? static::$entityClass;

        return $this->getDoctrine()->getRepository($entityClass)->findBy([], ['rank' => 'ASC']);
    }

    /**
     * @param Request $request
     * @param string  $entitySearchTypeClass
     * @param string  $entityClass
     * @param array  $serializationGroups
     * @param SearchModel $model
     * @param string  $methodName
     *
     * @return Response
     */
    protected function getEntityListSearchAction(Request $request,
                                                 string $entitySearchTypeClass = null,
                                                 string $entityClass = null,
                                                 array $serializationGroups = null,
                                                 SearchModel $model = null,
                                                 string $methodName = 'search'): ?Response
    {
        $entitySearchTypeClass = $entitySearchTypeClass ?? static::$entitySearchTypeClass;
        $serializationGroups = $serializationGroups ?? static::$serializationGroups;
        $entityClass = $entityClass ?? static::$entityClass;

        $form = $this->createForm($entitySearchTypeClass, $model, ['method' => 'GET']);
        $form->submit($request->query->all());

        if ($form->isValid()) {
            $repo = $this->getDoctrine()->getRepository($entityClass);
            $results = $repo->$methodName($form->getData());
            $nbResults = (int) $repo->$methodName($form->getData(), true);

            return $this->createPaginateResponse($results, $nbResults, $serializationGroups);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @param Request $request
     * @param mixed   $entity          The entity
     * @param string  $entityTypeClass Form Type
     *
     * @return View
     */
    protected function createEntityAction(Request $request, $entity = null, string $entityTypeClass = null): ?View
    {
        $form = $this->createForm($entityTypeClass ?? static::$entityTypeClass, $entity);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            return View::create($this->persistAndFlush($form->getData()), Response::HTTP_CREATED);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @param Request $request
     * @param $entity
     * @param string $entityTypeClass Form Type
     *
     * @return mixed
     */
    protected function updateEntityAction(Request $request, $entity, string $entityTypeClass = null)
    {
        $form = $this->createForm($entityTypeClass ?? static::$entityTypeClass, $entity);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            return $this->persistAndFlush($entity);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @param $entity
     *
     * @return View
     */
    protected function deleteEntityAction($entity)
    {
       $this->removeAndFlush($entity);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $class
     *
     * @return SerializedForm
     */
    protected function serializeForm(string $class)
    {
        $describer = new FormSerializer(
            $this->getDoctrine(),
            $this->container->get('form.factory'),
            $this->container->get('router')
        );

        return $describer->normalize($this->createForm($class));
    }

    /**
     * @param array $results
     * @param int   $nbResults
     * @param array $groups
     * @param array $headers
     *
     * @return Response
     */
    protected function createPaginateResponse(array $results, int $nbResults, array $groups = ['Default'], array $headers = [])
    {
        $serializer = $this->container->get('serializer');
        $data = $serializer->serialize($results, 'json', ['groups' => $groups]);
        $headers['X-Total-Results'] = $nbResults;

        $response = new Response($data);

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        $response->headers->set('Access-Control-Expose-Headers', implode(', ', array_keys($headers)));

        return $response;
    }

    /**
     * @param Form $form
     */
    protected function throwUnprocessableEntity(Form $form)
    {
        throw new ApiProblemException(
            new ApiProblem(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->get('app.form_errors_serializer')->serializeFormErrors($form, true, true)
            )
        );
    }

    /**
     * @param string $content
     *
     * @return Response
     */
    protected function renderResponse(string $content)
    {
        $response = new Response();

        $response->setContent($content);

        return $response;
    }

}