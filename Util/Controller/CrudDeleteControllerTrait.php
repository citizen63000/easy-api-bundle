<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

trait CrudDeleteControllerTrait
{
    /**
     * Delete entity.
     *
     *
     * @OA\Response(
     *     response=204,
     *     description="Successful operation",
     * ),
     * @OA\Response(response="404", description="Entity not found"),
     */
    #[Route(path: '/{id}', name: '_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request): Response
    {
        $this->checkDeleteRoles();
        
        return $this->doDeleteEntity($this->getEntityOfRequest($request));
    }
}
