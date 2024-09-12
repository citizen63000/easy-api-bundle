<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

trait CrudCreateControllerTrait
{
    /**
     * Create entity.
     *
     *
     * @OA\Parameter(
     *     name="data",
     *     in="query",
     *     description="Create data.",
     *     required=true,
     *     @OA\Schema(ref=@Nelmio\ApiDocBundle\Annotation\Model(type=self::entityCreateTypeClass))
     * ),
     * @OA\Response(
     *     response=201,
     *     description="Successful operation",
     *     @Nelmio\ApiDocBundle\Annotation\Model(
     *          type=self::entityClass,
     *          groups=self::serializationGroups
     *      )
     * ),
     * @OA\Response(response="405", description="Method not allowed"),
     */
    #[Route(name: '_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $this->checkCreateRoles();
        
        return $this->doCreateEntity($request);
    }
}
