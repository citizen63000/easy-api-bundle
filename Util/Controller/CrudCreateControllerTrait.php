<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CrudCreateControllerTrait
{
    /**
     * Create entity.
     *
     * @Symfony\Component\Routing\Annotation\Route(methods={"POST"}, name="_create")
     *
     * @OpenApi\Annotations\Parameter(
     *     name="data",
     *     in="body",
     *     description="Create data.",
     *     required=true,
     *     @OpenApi\Annotations\Schema(ref=@Nelmio\ApiDocBundle\Annotation\Model(type="static::entityCreateTypeClass"))
     * ),
     * @OpenApi\Annotations\Response(
     *     response=201,
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
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        return $this->createEntityAction($request);
    }
}
