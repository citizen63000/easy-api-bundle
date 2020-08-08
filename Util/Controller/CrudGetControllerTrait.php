<?php

namespace EasyApiBundle\Util\Controller;

trait CrudGetControllerTrait
{
    /**
     * Get entity.
     *
     * @Swagger\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Swagger\Annotations\Schema(
     *          ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *              type=AbstractApiController::entityClass,
     *              groups=AbstractApiController::serializationGroups
     *          )
     *      )
     * ),
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Symfony\Component\HttpFoundation\Request $request)
    {
        return $this->getEntityAction($this->getEntityOfRequest($request));
    }
}