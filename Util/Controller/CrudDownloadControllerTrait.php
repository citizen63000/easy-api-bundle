<?php


namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CrudDownloadControllerTrait
{
    /**
     * Download File of entity.
     *
     * @Symfony\Component\Routing\Annotation\Route("/{id}/download", methods={"GET"}, requirements={"id"="\d+"}, name="_download", )
     *
     * @OpenApi\Annotations\Response(
     *     response=200,
     *     description="Successful operation"
     * ),
     * @OpenApi\Annotations\Response(response="404", description="Entity not found"),
     * @OpenApi\Annotations\Response(response="405", description="Method not allowed"),
     * @param Request $request
     *
     * @return Response
     */
    public function downloadAction(Request $request): Response
    {
        return $this->downloadMediaAction($this->getEntityOfRequest($request));
    }
}