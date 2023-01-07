<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CrudGetControllerTrait
{
    /**
     * Get entity.
     *
     * @Symfony\Component\Routing\Annotation\Route("/{id}", methods={"GET"}, requirements={"id"="\d+"}, name="_get", )
     *
     * @OpenApi\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Nelmio\ApiDocBundle\Annotation\Model(
     *          type=self::entityClass,
     *          groups=self::serializationGroups
     *      )
     * ),
     * @OpenApi\Annotations\Response(response="404", description="Entity not found"),
     * @OpenApi\Annotations\Response(response="405", description="Method not allowed"),
     * @param Request $request
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        return $this->getEntityAction($request, $this->getEntityOfRequest($request));
    }
}
