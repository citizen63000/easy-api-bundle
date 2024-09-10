<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

trait CrudUpdateControllerTrait
{
    /**
     * Update Entity.
     *
     * @OpenApi\Annotations\Parameter(
     *     name="data",
     *     in="query",
     *     description="Create data.",
     *     required=true,
     *     @OpenApi\Annotations\Schema(ref=@Nelmio\ApiDocBundle\Annotation\Model(type=self::entityUpdateTypeClass))
     * ),
     * @OpenApi\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Nelmio\ApiDocBundle\Annotation\Model(
     *          type=self::entityClass,
     *          groups=self::serializationGroups
     *      )
     * ),
     * @OpenApi\Annotations\Response(response="404", description="Entity not found"),
     * @OpenApi\Annotations\Response(response="405", description="Method not allowed"),
     */
    #[Route(path: '/{id}', name: '_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(Request $request): Response
    {
        return $this->doUpdateEntity($request, $this->getEntityOfRequest($request));
    }
}
