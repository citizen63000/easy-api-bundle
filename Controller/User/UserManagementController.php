<?php

namespace EasyApiBundle\Controller\User;

use Nelmio\ApiDocBundle\Annotation\Model;
use EasyApiBundle\Controller\AbstractApiController;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * @Route("/user", name="api_user")
 */
class UserManagementController extends AbstractApiController
{
    public const array serializationAttributes = ['id', 'username', 'email', 'createdAt', 'updatedAt'];

    /**
     * Get me
     *
     * @Route("/me", methods={"GET"}, name="_get_me", )
     *
     * @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Model(type=self::entityClass, groups=self::serializationGroups)
     * ),
     *
     * @OA\Response(response="404", description="Entity not found"),
     * @OA\Response(response="405", description="Method not allowed"),
     */
    public function getMe(): Response
    {
        return $this->renderEntityResponse($this->getUser(), static::serializationGroups, [AbstractNormalizer::ATTRIBUTES => static::serializationAttributes]);
    }
}
