<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CrudCloneControllerTrait
{
    /**
     * Clone entity.
     *
     * @Symfony\Component\Routing\Annotation\Route("/clone/{id}", methods={"POST"}, name="_clone")
     *
     * @OpenApi\Annotations\Response(
     *     response=201,
     *     description="Successful operation",
     *     @Nelmio\ApiDocBundle\Annotation\Model(
     *          type=self::entityClass,
     *          groups=self::serializationGroups
     *      )
     * ),
     * @OpenApi\Annotations\Response(response="404", description="Entity not found"),
     * @OpenApi\Annotations\Response(response="405", description="Method not allowed"),
     */
    public function clone(Request $request): Response
    {
        return $this->doCloneEntity($this->getEntityOfRequest($request));
    }
}
