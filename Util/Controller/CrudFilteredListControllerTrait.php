<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CrudFilteredListControllerTrait
{
    /**
     * List entities.
     *
     * @Symfony\Component\Routing\Annotation\Route(methods={"GET"}, name="_list")
     *
     * @EasyApiBundle\Annotation\GetFormFilterParameter(
     *     type="static::entityFilterTypeClass",
     *     entityClass="static::entityClass",
     *     fields={"static::filterFields"},
     *     sortFields={"static::filterSortFields"}
     *  )
     * @OpenApi\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OpenApi\Annotations\Schema(
     *         type="array",
     *         @OpenApi\Annotations\Items(
     *              ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *                  type="static::entityClass",
     *                  groups={"static::listSerializationGroups"}
     *              )
     *          )
     *     )
     * )
     * @OpenApi\Annotations\Response(response="404", ref="#/definitions/404"),
     * @OpenApi\Annotations\Response(response="405", ref="#/definitions/405"),
     * @OpenApi\Annotations\Response(response="415", ref="#/definitions/415")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        return $this->getEntityFilteredListAction($request);
    }
}
