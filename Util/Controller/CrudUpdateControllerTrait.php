<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CrudUpdateControllerTrait
{
    /**
     * Update Entity.
     *
     * @Symfony\Component\Routing\Annotation\Route("/{id}", methods={"PUT"}, requirements={"id"="\d+"}, name="_update")
     *
     * @OpenApi\Annotations\Parameter(
     *     name="data",
     *     in="body",
     *     description="Create data.",
     *     required=true,
     *     @OpenApi\Annotations\Schema(ref=@Nelmio\ApiDocBundle\Annotation\Model(type=self::entityUpdateTypeClass)
     * ),
     * @OpenApi\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Nelmio\ApiDocBundle\Annotation\Model(
     *          type=self::entityClass,
     *          groups=self::serializationGroups
     *      )
     * ),
     * @OpenApi\Annotations\Response(response="404", ref="#/definitions/404"),
     * @OpenApi\Annotations\Response(response="405", ref="#/definitions/405"),
     * @OpenApi\Annotations\Response(response="415", ref="#/definitions/415")
     *
     * @param Request          $request
     *
     * @return Response
     */
    public function updateAction(Request $request)
    {
        return $this->updateEntityAction($request, $this->getEntityOfRequest($request));
    }
}
