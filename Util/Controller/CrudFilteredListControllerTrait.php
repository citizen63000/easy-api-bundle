<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

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
     * @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(
     *              ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *                  type=self::entityClass,
     *                  groups=self::listSerializationGroups
     *              )
     *          )
     *     )
     * )
     * @OA\Response(response="405", description="Method not allowed"),
     */
    public function list(Request $request): Response
    {
        return $this->doGetEntityFilteredList($request);
    }
}
