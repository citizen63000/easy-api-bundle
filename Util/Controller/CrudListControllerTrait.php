<?php

namespace EasyApiBundle\Util\Controller;

trait CrudListControllerTrait
{
    /**
     * Create entity.
     *
     * @Swagger\Annotations\Parameter(
     *     name="data",
     *     in="body",
     *     description="Create data.",
     *     required=true,
     *     @Swagger\Annotations\Schema(ref=@Nelmio\ApiDocBundle\Annotation\Model(type=EasyApiBundle\Util\Controller\AbstractApiController::entityTypeClass))
     * ),
     * @Swagger\Annotations\Response(
     *     response=201,
     *     description="Successful operation",
     *     @Swagger\Annotations\Schema(
     *         type="array",
     *         @Swagger\Items(
     *              ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *                  type=EasyApiBundle\Util\Controller\AbstractApiController::entityClass,
     *                  groups=EasyApiBundle\Util\Controller\AbstractApiController::serializationGroups
     *              )
     *          )
     *     )
     * ),
     * @Swagger\Annotations\Response(response="404", ref="#/definitions/404"),
     * @Swagger\Annotations\Response(response="405", ref="#/definitions/405"),
     * @Swagger\Annotations\Response(response="415", ref="#/definitions/415")
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createForEntityAction(Symfony\Component\HttpFoundation\Request $request)
    {
        return $this->getEntityListSearchAction($request, static::entitySearchTypeClass, static::entityClass, static::serializationGroups);
    }
}