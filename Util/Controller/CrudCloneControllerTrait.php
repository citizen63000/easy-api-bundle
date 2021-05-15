<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @WIP
 */
trait CrudCloneControllerTrait
{
    /**
     * Clone entity.
     *
     * @Symfony\Component\Routing\Annotation\Route("/{id}", methods={"POST"}, name="_clone")
     *
     * @Swagger\Annotations\Response(
     *     response=201,
     *     description="Successful operation",
     *     @Swagger\Annotations\Schema(
     *          ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *              type="static::entityClass",
     *              groups={"static::serializationGroups"}
     *          )
     *      )
     * ),
     * @Swagger\Annotations\Response(response="404", ref="#/definitions/404"),
     * @Swagger\Annotations\Response(response="405", ref="#/definitions/405"),
     * @Swagger\Annotations\Response(response="415", ref="#/definitions/415")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cloneAction(Request $request)
    {
        return $this->cloneEntityAction($request);
    }
}
