<?php

namespace EasyApiBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use EasyApiBundle\Entity\AbstractBaseEntity;
use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Form\Serializer\FormErrorsSerializer;
use EasyApiBundle\Form\Type\FilterType;
use EasyApiBundle\Model\FilterResult;
use EasyApiBundle\Services\EntitySerializer;
use EasyApiBundle\Services\ListFilter;
use EasyApiBundle\Services\MediaUploader\FileManager;
use EasyApiBundle\Util\ApiProblem;
use EasyApiCore\Util\CoreUtilsTrait;
use EasyApiCore\Util\Forms\FormSerializer;
use EasyApiCore\Util\Forms\SerializedForm;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @method UserInterface|null getUser() Gets the current User.
 */
abstract class AbstractApiController extends AbstractFOSRestController
{
    use CoreUtilsTrait;

    /** @var ?string */
    public const entityClass = null;

    /** @var ?string */
    public const entityCreateTypeClass = null;

    /** @var ?string */
    public const entityUpdateTypeClass = null;

    /** @var array */
    public const serializationGroups = [];

    /** @var array */
    public const serializationAttributes = [];

    /** @var array */
    public const listSerializationGroups = [];

    /** @var array */
    public const filterFields = [];

    /** @var array */
    public const filterSortFields = [];

    /** @var string */
    public const entityFilterModelClass = FilterModel::class;

    /** @var string */
    public const entityFilterTypeClass = FilterType::class;

    /** @var string */
    public const filterService = ListFilter::class;

    /** @var ?array */
    public const defaultFilterSort = null;

    /** @var bool */
    public const useSerializerCache = false;
    
    protected static array $readRoles = [];
    protected static array $createRoles = [];
    protected static array $updateRoles = [];
    protected static array $deleteRoles = [];
    protected static array $listRoles = [];
    protected static array $cloneRoles = [];
    protected static array $downloadRoles = [];

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function getDoctrine(): ?ManagerRegistry
    {
        try {
            return $this->container->get('doctrine');
        } catch (\Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return null;
        }
    }

    protected function getEntityOfRequest(Request $request): ?object
    {
        $entity = $this->getRepository(static::entityClass)->find($request->get('id'));

        if (null === $entity) {
            throw new NotFoundHttpException(sprintf(ApiProblem::ENTITY_NOT_FOUND, 'entity'));
        }

        return $entity;
    }

    protected function doGetEntity(Request $request, $entity, array $serializationGroups = null, array $serializationAttributes = null): Response
    {
        return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups, $serializationAttributes ?? static::serializationAttributes, [], Response::HTTP_OK, [], static::useSerializerCache);
    }

    protected function getEntityListAction(string $entityClass = null, array $serializationGroups = null, array $serializationAttributes = null): Response
    {
        $entities = $this->getRepository($entityClass ?? static::entityClass)->findAll();

        return static::renderEntityResponse($entities, $serializationGroups ?? static::listSerializationGroups, $serializationAttributes ?? static::serializationAttributes);
    }

    protected function getEntityListOrderedAction(string $entityClass = null, array $serializationGroups = null, array $serializationAttributes = null): Response
    {
        $entities = $this->getRepository($entityClass ?? static::entityClass)->findBy([], ['rank' => 'ASC']);

        return static::renderEntityResponse($entities, $serializationGroups ?? static::listSerializationGroups, $serializationAttributes ?? static::serializationAttributes);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function doGetEntityFilteredList(
        Request $request,
        string $entityFilterTypeClass = null,
        string $entityClass = null,
        array $fields = null,
        array $sortFields = null,
        array $serializationGroups = null,
        array $serializationAttributes = null,
        FilterModel $entityFilterModel = null
    ): Response {
        // type & model
        $entityFilterTypeClass = $entityFilterTypeClass ?? static::entityFilterTypeClass;
        $entityFilterModelClass = static::entityFilterModelClass;
        $entityFilterModel = $entityFilterModel ?? new $entityFilterModelClass();
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
            } catch (NoResultException|NonUniqueResultException $e) {
                $result = new FilterResult();
            }

            return $this->createPaginateResponse($result->getResults(), $result->getNbResults(), $serializationGroups, $serializationAttributes);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @throws \Exception
     */
    protected function doCreateEntity(Request $request, $entity = null, string $entityTypeClass = null, array $serializationGroups = null, array $serializationAttributes = null): Response
    {
        $form = $this->createForm($entityTypeClass ?? static::entityCreateTypeClass, $entity);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $entity = $this->persistAndFlush($form->getData());

            if (static::useSerializerCache) {
                $this->clearSerializerCache($entity);
            }

            return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups, $serializationAttributes ?? static::serializationAttributes, [], Response::HTTP_CREATED);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @throws \Exception
     */
    protected function doUpdateEntity(Request $request, $entity, string $entityTypeClass = null, array $serializationGroups = null, array $serializationAttributes = null): Response
    {
        $form = $this->createForm($entityTypeClass ?? static::entityUpdateTypeClass, $entity);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $entity = $this->persistAndFlush($entity);

            if (static::useSerializerCache) {
                $this->clearSerializerCache($entity);
            }

            return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups, $serializationAttributes ?? static::serializationAttributes, [], Response::HTTP_OK);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * @throws \Exception
     */
    protected function doDeleteEntity($entity): Response
    {
        if (static::useSerializerCache) {
            $this->clearSerializerCache($entity);
        }

        $this->removeAndFlush($entity);

        return static::renderResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws \Exception
     */
    protected function doCloneEntity($entity, array $serializationGroups = null, array $serializationAttributes = null): Response
    {
        $entity = $this->persistAndFlush(clone $entity);

        return static::renderEntityResponse($entity, $serializationGroups ?? static::serializationGroups, $serializationAttributes ?? static::serializationAttributes, [], Response::HTTP_CREATED);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function doDownloadMedia(AbstractMedia $entity): Response
    {
        return $this->renderFileStreamedResponse($this->container->get(FileManager::class)->getFileSystemPath($entity), $entity->getFilename());
    }

    protected function doGetDescribeForm(Request $request): Response
    {
        $method = strtoupper($request->query->get('method', 'POST'));

        $form = 'POST' === $method ? static::entityCreateTypeClass : static::entityUpdateTypeClass;

        return $this->renderEntityResponse($this->serializeForm($form), ['public']);
    }

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createPaginateResponse(array $results, int $nbResults, array $serializationGroups = null, array $serializationAttributes = null, array $headers = []): Response
    {
        $context = [];

        if (null !== $serializationAttributes) {
            $context[AbstractNormalizer::ATTRIBUTES] = $serializationAttributes;
        } else {
            $context[AbstractNormalizer::GROUPS] = $serializationGroups ?? ['Default'];
        }

        $serializer = $this->container->get('serializer');
        $data = $serializer->serialize($results, 'json', $context);
        $headers['X-Total-Results'] = $nbResults;

        $response = new Response($data);

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        $response->headers->set('Access-Control-Expose-Headers', implode(', ', array_keys($headers)));

        return $response;
    }

    /**
     * @throws ApiProblemException
     */
    protected function throwUnprocessableEntity(Form $form)
    {
        throw new ApiProblemException(
            new ApiProblem(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->get(FormErrorsSerializer::class)->serializeFormErrors($form, true, true)
            )
        );
    }

    protected function renderResponse(?string $content, int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }

    /**
     * @param $entity
     * @param array|null $serializationGroups
     * @param array|null $serializationAttributes
     * @param array|null $context Additional context like AbstractNormalizer::IGNORED_ATTRIBUTES list or AbstractNormalizer::ATTRIBUTES list
     * @param int $status
     * @param array $headers
     * @param bool $useCache
     * @return Response
     */
    protected function renderEntityResponse($entity, array $serializationGroups = null, array $serializationAttributes = null, array $context = null, int $status = 200, array $headers = [], bool $useCache = false): Response
    {
        $context = $context ?? [];

        if (null !== $serializationGroups) {
            $context[AbstractNormalizer::GROUPS] = $serializationGroups;
        }

        if (!empty($serializationAttributes)) {
            $context[AbstractNormalizer::ATTRIBUTES] = $serializationAttributes;
        }

        return $this->renderResponse($this->serializeEntity($entity, $context, $useCache), $status, $headers);
    }

    protected function serializeEntity($entity, array $context, bool $useCache = false, bool $forceCacheReload = false): string
    {
        if ($entity instanceof AbstractBaseEntity) {
            return $this->get(EntitySerializer::class)->serializeEntity($entity, $context, JsonEncoder::FORMAT, $useCache, $forceCacheReload);
        } else {
            return $this->getSerializer()->serialize($entity, JsonEncoder::FORMAT, $context);
        }
    }

    protected function clearSerializerCache($entity): void
    {
        if (static::useSerializerCache) {
            try {
                $this->get(EntitySerializer::class)->clearCache($entity);
            } catch (\Exception|InvalidArgumentException $e) {
            }
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function renderFileStreamedResponse(string $path, string $filename): Response
    {
        return $this->container->get(FileManager::class)->getFileStreamedResponse($path, $filename);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->container->get('serializer');
    }

    /**
     * Add dynamically needed services.
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            static::filterService => static::filterService,
            FileManager::class => FileManager::class,
            EntitySerializer::class => EntitySerializer::class,
            FormErrorsSerializer::class => FormErrorsSerializer::class,
        ]);
    }
    
    protected function checkReadRoles(): void
    {
        if (null !== static::$readRoles && static::$readRoles !== []) {
            foreach (static::$readRoles as $role) {
                $this->denyAccessUnlessGranted($role);
            }
        }
    }

    protected function checkCreateRoles(): void
    {
        if (null !== static::$createRoles && static::$createRoles !== []) {
            foreach (static::$createRoles as $role) {
                $this->denyAccessUnlessGranted($role);
            }
        }
    }

    protected function checkUpdateRoles(): void
    {
        if (null !== static::$updateRoles && static::$updateRoles !== []) {
            foreach (static::$updateRoles as $role) {
                $this->denyAccessUnlessGranted($role);
            }
        }
    }

    protected function checkDeleteRoles(): void
    {
        if (null !== static::$deleteRoles && static::$deleteRoles !== []) {
            foreach (static::$deleteRoles as $role) {
                $this->denyAccessUnlessGranted($role);
            }
        }
    }

    protected function checkListRoles(): void
    {
        if (null !== static::$listRoles && static::$listRoles !== []) {
            foreach (static::$listRoles as $role) {
                $this->denyAccessUnlessGranted($role);
            }
        }
    }

    protected function checkCloneRoles(): void
    {
        if (null !== static::$cloneRoles && static::$cloneRoles !== []) {
            foreach (static::$cloneRoles as $role) {
                $this->denyAccessUnlessGranted($role);
            }
        }
    }

    protected function checkDownloadRoles(): void
    {
        if (null !== static::$downloadRoles && static::$downloadRoles !== []) {
            foreach (static::$downloadRoles as $role) {
                $this->denyAccessUnlessGranted($role);
            }
        }
    }
}
