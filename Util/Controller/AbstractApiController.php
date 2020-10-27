<?php

namespace EasyApiBundle\Util\Controller;

use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Form\Type\FilterType;
use EasyApiBundle\Services\ListFilter;
use EasyApiBundle\Util\Forms\FormSerializer;
use EasyApiBundle\Util\Forms\SerializedForm;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Model\User;
use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\CoreUtilsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractApiController extends FOSRestController
{
    use CoreUtilsTrait;

    /** @var null */
    public const entityClass = null;

    /** @var null */
    public const entityCreateTypeClass = null;

    /** @var null */
    public const entityUpdateTypeClass = null;

    /** @var array */
    public const serializationGroups = [];

    /** @var array  */
    public const filterFields = [];

    /** @var array */
    public const filterSortFields = [];

    /** @var string */
    public const entityFilterModelClass = FilterModel::class;

    /** @var string */
    public const entityFilterTypeClass = FilterType::class;

    /** @var string */
    public const filterService = ListFilter::class;

    /** @var null */
    public const defaultFilterSort = null;

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
     * @param Request $request
     * @return null|object
     */
    protected function getEntityOfRequest(Request $request)
    {
        $entity = $this->getRepository(static::entityClass)->find($request->get('id'));

        if(null === $entity) {
            throw new NotFoundHttpException(sprintf(ApiProblem::ENTITY_NOT_FOUND, 'entity'));
        }
        
        return $entity;
    }

    /**
     * @param $entity
     * @param array|null $serializationGroups
     *
     * @return Response
     */
    protected function getEntityAction($entity, array $serializationGroups = null): Response
    {
        return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups);
    }

    /**
     * @param string|null $entityClass
     * @param array|null $serializationGroups
     *
     * @return Response
     */
    protected function getEntityListAction(string $entityClass = null, array $serializationGroups = null): Response
    {
        $entityClass = $entityClass ?? static::entityClass;

        $entities = $this->getDoctrine()->getRepository($entityClass)->findAll();

        return static::renderEntityResponse($entities, $serializationGroups ?? static::serializationGroups);
    }

    /**
     * @param string|null $entityClass
     * @param array|null $serializationGroups
     *
     * @return Response
     */
    protected function getEntityListOrderedAction(string $entityClass = null, array $serializationGroups = null): Response
    {
        $entityClass = $entityClass ?? static::entityClass;

        $entities = $this->getDoctrine()->getRepository($entityClass)->findBy([], ['rank' => 'ASC']);

        return static::renderEntityResponse($entities, $serializationGroups ?? static::serializationGroups);
    }

    /**
     * @param Request $request
     * @param string|null $entityFilterTypeClass
     * @param string|null $entityClass
     * @param array|null $fields
     * @param array|null $sortFields
     * @param array|null $serializationGroups
     * @param FilterModel|null $entityFilterModel
     *
     * @return Response|null
     */
    protected function getEntityFilteredListAction(Request $request,
                                                   string $entityFilterTypeClass = null,
                                                   string $entityClass = null,
                                                   array $fields = null,
                                                   array $sortFields = null,
                                                   array $serializationGroups = null,
                                                   FilterModel $entityFilterModel = null): Response
    {
        $entityFilterTypeClass = $entityFilterTypeClass ?? static::entityFilterTypeClass;
        $entityFilterModelClass = static::entityFilterModelClass;
        $entityFilterModel = $entityFilterModel ?? new $entityFilterModelClass;
        $entityFilterModel->setDefaultSort(static::defaultFilterSort);
        $serializationGroups = $serializationGroups ?? static::serializationGroups;
        $entityClass = $entityClass ?? static::entityClass;
        $fields = $fields ?? static::filterFields;
        $sortFields = $sortFields ?? static::filterSortFields;

        $formOptions = ['method' => 'GET', 'entityClass' => $entityClass, 'fields' => $fields, 'sortFields' => $sortFields];

        $form = $this->createForm($entityFilterTypeClass, $entityFilterModel, $formOptions);
        $form->submit($request->query->all());

        if ($form->isValid()) {

            $result = $this->get(static::filterService)->filter($form, $entityClass);

            return $this->createPaginateResponse($result->getResults(), $result->getNbResults(), $serializationGroups);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @param Request $request
     * @param null $entity
     * @param string|null $entityTypeClass
     * @param array|null $serializationGroups
     * @return Response
     */
    protected function createEntityAction(Request $request, $entity = null, string $entityTypeClass = null, array $serializationGroups = null): Response
    {
        $form = $this->createForm($entityTypeClass ?? static::entityCreateTypeClass, $entity);

        $form->submit($request->request->all());

        if ($form->isValid()) {

            $entity = $this->persistAndFlush($form->getData());

            return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups, Response::HTTP_CREATED);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @param Request $request
     * @param $entity
     * @param string|null $entityTypeClass
     * @param array|null $serializationGroups
     * @return Response
     */
    protected function updateEntityAction(Request $request, $entity, string $entityTypeClass = null, array $serializationGroups = null): Response
    {
        $form = $this->createForm($entityTypeClass ?? static::entityUpdateTypeClass, $entity);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {

            $entity = $this->persistAndFlush($entity);

            return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups, Response::HTTP_OK);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @param $entity
     *
     * @return Response
     */
    protected function deleteEntityAction($entity)
    {
        $this->removeAndFlush($entity);

        return static::renderResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function getDescribeFormAction(Request $request)
    {
        $method = strtoupper($request->query->get('method', 'POST'));

        $form = 'POST' === $method ? static::entityCreateTypeClass : static::entityUpdateTypeClass;

        return $this->renderEntityResponse($this->describeForm($form), ['public']);
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
     * @param array $serializationGroups
     * @param array $headers
     *
     * @return Response
     */
    protected function createPaginateResponse(array $results, int $nbResults, array $serializationGroups = ['Default'], array $headers = [])
    {
        $serializer = $this->container->get('serializer');
        $data = $serializer->serialize($results, 'json', ['groups' => $serializationGroups]);
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
     * @param string|null $content
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    protected function renderResponse(?string $content, int $status = 200, array $headers = [])
    {
        return (new Response($content, $status, $headers));
    }

    /**
     * @param $entity
     * @param array|null $serializationGroups
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    protected function renderEntityResponse($entity, array $serializationGroups = null, int $status = 200, array $headers = [])
    {
        $serializer = $this->container->get('serializer');
        $data = $serializer->serialize($entity, 'json', (null !== $serializationGroups ? ['groups' => $serializationGroups] : []));

        return $this->renderResponse($data, $status, $headers);
    }

}
