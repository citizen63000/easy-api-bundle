<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use EasyApiBundle\Util\Forms\SerializedForm;

trait CrudDescribeFormControllerTrait
{
    /**
     * Describe fields of form.
     *
     * @Symfony\Component\Routing\Annotation\Route("/describeForm", methods={"GET"}, name="describe_form")
     *
     * @Swagger\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Swagger\Annotations\Schema(ref=@Nelmio\ApiDocBundle\Annotation\Model(type="EasyApiBundle\Util\Forms\SerializedForm", groups={"public"}))
     * )
     *
     * @Swagger\Annotations\Response(response="404", ref="#/definitions/404")
     * @Swagger\Annotations\Response(response="405", ref="#/definitions/405")
     * @Swagger\Annotations\Response(response="415", ref="#/definitions/415")
     *
     * @FOS\RestBundle\Controller\Annotations\View(serializerGroups={ "public" })
     *
     * @param Request $request
     *
     * @return SerializedForm
     */
    public function describeFormAction(Request $request): SerializedForm
    {
        $method = strtoupper($request->query->get('method', 'POST'));

        $form = 'POST' === $method ? static::entityCreateTypeClass : static::entityUpdateTypeClass;

        return $this->describeForm($form);
    }
}
