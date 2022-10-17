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
     *     @OpenApi\Annotations\Schema(
     *          ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *              type="static::entityClass",
     *              groups={"static::serializationGroups"}
     *          )
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
    public function cloneAction(Request $request)
    {
        return $this->cloneEntityAction($this->getEntityOfRequest($request));
    }
}
