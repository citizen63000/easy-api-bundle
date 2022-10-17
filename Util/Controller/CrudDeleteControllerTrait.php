<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CrudDeleteControllerTrait
{
    /**
     * Delete entity.
     *
     * @Symfony\Component\Routing\Annotation\Route("/{id}", methods={"DELETE"}, requirements={"id"="\d+"}, name="_delete")
     *
     * @OpenApi\Annotations\Response(
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
