<?php

namespace EasyApiBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Form\Type\FilterType;
use EasyApiBundle\Model\FilterResult;
use EasyApiBundle\Services\ListFilter;
use EasyApiBundle\Services\MediaUploader\FileManager;
use EasyApiBundle\Util\Forms\FormSerializer;
use EasyApiBundle\Util\Forms\SerializedForm;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\UserBundle\Model\User;
use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\CoreUtilsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class AbstractApiController
 * @package EasyApiBundle\Controller
 * @method User|null getUser() Gets the current User.
 */
abstract class AbstractApiController extends AbstractFOSRestController
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

    /** @var array */
    public const serializationAttributes = [];

    /** @var array */
    public const listSerializationGroups = [];

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
     * @param Request $request
     *
     * @return null|object
     *
     * @throws NotFoundHttpException
     */
    protected function getEntityOfRequest(Request $request)
    {
        $entity = $this->getRepository(static::entityClass)->find($request->get('id'));

        if (null === $entity) {
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
        $entities = $this->getRepository($entityClass ?? static::entityClass)->findAll();

        return static::renderEntityResponse($entities, $serializationGroups ?? static::listSerializationGroups);
    }

    /**
     * @param string|null $entityClass
     * @param array|null $serializationGroups
     *
     * @return Response
     */
    protected function getEntityListOrderedAction(string $entityClass = null, array $serializationGroups = null): Response
    {
        $entities = $this->getRepository($entityClass ?? static::entityClass)->findBy([], ['rank' => 'ASC']);

        return static::renderEntityResponse($entities, $serializationGroups ?? static::listSerializationGroups);
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
    protected function getEntityFilteredListAction(
        Request $request,
        string $entityFilterTypeClass = null,
        string $entityClass = null,
        array $fields = null,
        array $sortFields = null,
        array $serializationGroups = null,
        FilterModel $entityFilterModel = null
    ): Response
    {
        // type & model
        $entityFilterTypeClass = $entityFilterTypeClass ?? static::entityFilterTypeClass;
        $entityFilterModelClass = static::entityFilterModelClass;
        $entityFilterModel = $entityFilterModel ??  new $entityFilterModelClass;
        $entityFilterModel->setDefaultSort(static::defaultFilterSort);
        // entity
        $entityClass = $entityClass ?? static::entityClass;
        $serializationGroups = $serializationGroups ?? static::listSerializationGroups;
        // filters
        $fields = $fields ?? static::filterFields;
        $sortFields = $sortFields ?? static::filterSortFields;
        //form
        $formOptions = ['method' => 'GET', 'entityClass' => $entityClass, 'fields' => $fields, 'sortFields' => $sortFields];
        $form = $this->createForm($entityFilterTypeClass, $entityFilterModel, $formOptions);
        $form->submit($request->query->all());

        if ($form->isValid()) {
            try {
                $result = $this->get(static::filterService)->filter($form, $entityClass);
            } catch (NoResultException | NonUniqueResultException $e) {
                $result = new FilterResult();
            }

            return $this->createPaginateResponse($result->getResults(), $result->getNbResults(), $serializationGroups);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @param Request $request
     * @param null $entity
     * @param string|null $entityTypeClass
     * @param array|null $serializationGroups
     *
     * @return Response
     */
    protected function createEntityAction(Request $request, $entity = null, string $entityTypeClass = null, array $serializationGroups = null): Response
    {
        $form = $this->createForm($entityTypeClass ?? static::entityCreateTypeClass, $entity);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $entity = $this->persistAndFlush($form->getData());

            return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups, [], Response::HTTP_CREATED);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @param Request $request
     * @param $entity
     * @param string|null $entityTypeClass
     * @param array|null $serializationGroups
     *
     * @return Response
     */
    protected function updateEntityAction(Request $request, $entity, string $entityTypeClass = null, array $serializationGroups = null): Response
    {
        $form = $this->createForm($entityTypeClass ?? static::entityUpdateTypeClass, $entity);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $entity = $this->persistAndFlush($entity);

            return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups, [], Response::HTTP_OK);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @param $entity
     *
     * @return Response
     */
    protected function deleteEntityAction($entity): Response
    {
        $this->removeAndFlush($entity);

        return static::renderResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param $entity
     * @param array|null $serializationGroups
     * @return Response
     */
    protected function cloneEntityAction($entity, array $serializationGroups = null): Response
    {
        $entity = $this->persistAndFlush(clone $entity);

        return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups, [], Response::HTTP_CREATED);
    }

    /**
     * @param $entity
     * @return Response
     */
    protected function downloadMediaAction(AbstractMedia $entity): Response
    {
        return $this->renderFileStreamedResponse($this->container->get(FileManager::class)->getFileSystemPath($entity), $entity->getFilename());
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function getDescribeFormAction(Request $request): Response
    {
        $method = strtoupper($request->query->get('method', 'POST'));

        $form = 'POST' === $method ? static::entityCreateTypeClass : static::entityUpdateTypeClass;

        return $this->renderEntityResponse($this->serializeForm($form), ['public']);
    }

    /**
     * @param string $class
     *
     * @return SerializedForm
     */
    protected function serializeForm(string $class): SerializedForm
    {
        $describer = new FormSerializer(
            $this->container->get('form.factory'),
            $this->container->get('router'),
            $this->getDoctrine()
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
    protected function createPaginateResponse(array $results, int $nbResults, array $serializationGroups = ['Default'], array $headers = []): Response
    {
        $serializer = $this->container->get('serializer');
        $data = $serializer->serialize($results, 'json', [AbstractNormalizer::GROUPS => $serializationGroups]);
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
     *
     * @throws ApiProblemException
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
    protected function renderResponse(?string $content, int $status = 200, array $headers = []): Response
    {
        return (new Response($content, $status, $headers));
    }

    /**
     * @param $entity
     * @param array|null $serializationGroups
     * @param array|null $context Additional context like AbstractNormalizer::IGNORED_ATTRIBUTES list or AbstractNormalizer::ATTRIBUTES list
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    protected function renderEntityResponse($entity, array $serializationGroups = null, array $context = null, int $status = 200, array $headers = []): Response
    {
        $context = $context ?? [];

        if (null !== $serializationGroups) {
            $context [AbstractNormalizer::GROUPS] = $serializationGroups;
        }

        return $this->renderResponse($this->getSerializer()->serialize($entity, 'json', $context), $status, $headers);
    }

    /**
     * @param string $path
     * @param string $filename
     * @return Response
     */
    protected function renderFileStreamedResponse(string $path, string $filename): Response
    {
        return $this->container->get(FileManager::class)->getFileStreamedResponse($path, $filename);
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->container->get('serializer');
    }

    /**
     * Add dynamically Filter service
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [static::filterService => static::filterService, FileManager::class => FileManager::class]);
    }
}
