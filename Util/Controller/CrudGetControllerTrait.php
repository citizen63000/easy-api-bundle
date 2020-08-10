<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     *              type="static::entityClass",
     *              groups={"static::serializationGroups"}
     *          )
     *      )
     * ),
     * @param Request $request
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        return $this->getEntityAction($this->getEntityOfRequest($request));
    }
}
