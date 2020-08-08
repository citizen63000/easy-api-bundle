<?php

namespace EasyApiBundle\Util\Controller;

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
     * @param Symfony\Component\HttpFoundation\Request $entity
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Symfony\Component\HttpFoundation\Request $request)
    {
        return $this->deleteEntityAction($this->getEntityOfRequest($request));
    }
}