<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CrudDeleteControllerTrait
{
    /**
     * Delete entity.
     *
     * @Swagger\Annotations\Response(
     *     response=204,
     *     description="Successful operation",
     * ),
     *
     * @param Request $entity
     *
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        return $this->deleteEntityAction($this->getEntityOfRequest($request));
    }
}
