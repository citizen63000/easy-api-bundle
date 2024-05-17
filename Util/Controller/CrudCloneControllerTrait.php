<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

trait CrudCloneControllerTrait
{
    /**
     * Clone entity.
     *
     * @Route("/clone/{id}", methods={"POST"}, name="_clone")
     *
     * @OA\Response(
     *     response=201,
     *     description="Successful operation",
     *     @Nelmio\ApiDocBundle\Annotation\Model(
     *          type=self::entityClass,
     *          groups=self::serializationGroups
     *      )
     * ),
     * @OA\Response(response="404", description="Entity not found"),
     * @OA\Response(response="405", description="Method not allowed"),
     */
    public function clone(Request $request): Response
    {
        $this->checkCloneRoles();

        return $this->doCloneEntity($this->getEntityOfRequest($request));
    }
}
