<?php

namespace EasyApiBundle\Util\Controller;

trait CrudDescribeFormControllerTrait
{
    /**
     * Describe fields of form.
     *
     * @Swagger\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Swagger\Annotations\Schema(ref=@Nelmio\ApiDocBundle\Annotation\Model(type="EasyApiBundle\Util\Forms\SerializedForm", groups={"public"}))
     * ),
     *
     * @Swagger\Annotations\Response(response="404", ref="#/definitions/404"),
     * @Swagger\Annotations\Response(response="405", ref="#/definitions/405"),
     * @Swagger\Annotations\Response(response="415", ref="#/definitions/415")
     *
     * @FOS\RestBundle\Controller\Annotations\View(serializerGroups={ "public" })
     *
     * @return EasyApiBundle\Util\Forms\SerializedForm
     */
    public function describeFormAction()
    {
        return $this->describeForm(static::entityTypeClass);
    }
}