<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Attribute\Route;

trait CrudFilteredListControllerTrait
{
    /**
     * List entities.
     *
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
    #[Route(name: '_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $this->checkListRoles();
        
        return $this->doGetEntityFilteredList($request);
    }
}
