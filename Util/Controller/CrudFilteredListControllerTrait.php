<?php

namespace EasyApiBundle\Util\Controller;

trait CrudFilteredListControllerTrait
{
    /**
     * List entities.
     *
     * @EasyApiBundle\Annotation\GetFormParameter(type="static::entitySearchTypeClass")
     * @Swagger\Annotations\Response(
     *     response=201,
     *     description="Successful operation",
     *     @Swagger\Annotations\Schema(
     *         type="array",
     *         @Swagger\Annotations\Items(
     *              ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *                  type="static::entityClass",
     *                  groups={"static::serializationGroups"}
     *              )
     *          )
     *     )
     * )
     * @Swagger\Annotations\Response(response="404", ref="#/definitions/404"),
     * @Swagger\Annotations\Response(response="405", ref="#/definitions/405"),
     * @Swagger\Annotations\Response(response="415", ref="#/definitions/415")
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Symfony\Component\HttpFoundation\Request $request)
    {
        return $this->getEntityFilteredListAction($request, static::entitySearchTypeClass, static::entityClass, static::serializationGroups);
    }
}