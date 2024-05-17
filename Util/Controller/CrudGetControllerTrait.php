<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

trait CrudGetControllerTrait
{
    /**
     * Get entity
     * 
     * @Route("/{id}", methods={"GET"}, requirements={"id"="\d+"}, name="_get")
     *
     * @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Nelmio\ApiDocBundle\Annotation\Model(
     *          type=self::entityClass,
     *          groups=self::serializationGroups
     *      )
     * ),
     * @OA\Response(response="404", description="Entity not found"),
     * @OA\Response(response="405", description="Method not allowed"),
     */
    public function read(Request $request): Response
    {
        $this->checkReadRoles();
        
        return $this->doGetEntity($request, $this->getEntityOfRequest($request));
    }
}
