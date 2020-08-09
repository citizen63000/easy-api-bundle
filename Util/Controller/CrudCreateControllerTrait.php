<?php


namespace EasyApiBundle\Util\Controller;


trait CrudCreateControllerTrait
{
    /**
     * Create entity.
     *
     * @Swagger\Annotations\Parameter(
     *     name="data",
     *     in="body",
     *     description="Create data.",
     *     required=true,
     *     @Swagger\Annotations\Schema(ref=@Nelmio\ApiDocBundle\Annotation\Model(type="static::entityTypeClass"))
     * ),
     * @Swagger\Annotations\Response(
     *     response=201,
     *     description="Successful operation",
     *     @Swagger\Annotations\Schema(
     *          ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *              type="static::entityClass",
     *              groups={"static::serializationGroups"}
     *          )
     *      )
     * ),
     * @Swagger\Annotations\Response(response="404", ref="#/definitions/404"),
     * @Swagger\Annotations\Response(response="405", ref="#/definitions/405"),
     * @Swagger\Annotations\Response(response="415", ref="#/definitions/415")
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Symfony\Component\HttpFoundation\Request $request)
    {
        return $this->createEntityAction($request);
    }
}