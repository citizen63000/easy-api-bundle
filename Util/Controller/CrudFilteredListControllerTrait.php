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
     * @Swagger\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Swagger\Annotations\Schema(
     *         type="array",
     *         @Swagger\Annotations\Items(
     *              ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *                  type="static::entityClass",
     *                  groups={"static::serializationGroups"}
     *              )
     *          )
     *     )
     * )
     * @Swagger\Annotations\Response(response="404", ref="#/definitions/404"),
     * @Swagger\Annotations\Response(response="405", ref="#/definitions/405"),
     * @Swagger\Annotations\Response(response="415", ref="#/definitions/415")
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
