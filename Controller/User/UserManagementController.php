<?php

namespace EasyApiBundle\Controller\User;

use Nelmio\ApiDocBundle\Annotation\Model;
use EasyApiBundle\Controller\AbstractApiController;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * @Route("/user", name="api_user")
 */
class UserManagementController extends AbstractApiController
{
    public const serializationAttributes = ['id', 'username', 'email', 'createdAt', 'updatedAt'];

    /**
     * Get me
     *
     * @Route("/me", methods={"GET"}, name="_get_me", )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(ref=@Model(type="static::entityClass", groups={"static::serializationGroups"}))
     * ),
     *
     * @SWG\Response(response="401", ref="#/definitions/401"),
     * @SWG\Response(response="403", ref="#/definitions/403"),
     * @SWG\Response(response="415", ref="#/definitions/415"),
     * @SWG\Response(response="422", ref="#/definitions/422")
     *
     * @return Response
     *
     */
    public function getMeAction(): Response
    {
        return $this->renderEntityResponse($this->getUser(), static::serializationGroups, [AbstractNormalizer::ATTRIBUTES => static::serializationAttributes]);
    }
}
