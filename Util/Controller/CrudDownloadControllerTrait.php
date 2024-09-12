<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

trait CrudDownloadControllerTrait
{
    /**
     * Download File of entity.
     *
     *
     * @OpenApi\Annotations\Response(
     *     response=200,
     *     description="Successful operation"
     * ),
     * @OpenApi\Annotations\Response(response="404", description="Entity not found"),
     * @OpenApi\Annotations\Response(response="405", description="Method not allowed"),
     */
    #[Route(path: '/{id}/download', name: '_download', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function download(Request $request): Response
    {
        $this->checkDownloadRoles();
        
        return $this->doDownloadMedia($this->getEntityOfRequest($request));
    }
}