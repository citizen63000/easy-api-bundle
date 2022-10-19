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
     *     type=self::entityFilterTypeClass,
     *     entityClass=self::entityClass,
     *     fields=self::filterFields,
     *     sortFields=self::filterSortFields
     *  )
     * @OpenApi\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OpenApi\Annotations\Schema(
     *         type="array",
     *         @OpenApi\Annotations\Items(
     *              ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *                  type=self::entityClass,
     *                  groups=self::listSerializationGroups
     *              )
     *          )
     *     )
     * )
     * @OpenApi\Annotations\Response(response="405", description="Method not allowed"),
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
