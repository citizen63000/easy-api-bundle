<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CrudDescribeFormControllerTrait
{
    /**
     * Describe fields of form.
     *
     * @Symfony\Component\Routing\Annotation\Route("/describeForm", methods={"GET"}, name="_describe_form")
     *
     * @OpenApi\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OpenApi\Annotations\Schema(ref=@Nelmio\ApiDocBundle\Annotation\Model(type="EasyApiBundle\Util\Forms\SerializedForm", groups={"public"}))
     * )
     *
     * @OpenApi\Annotations\Response(response="404", ref="#/definitions/404")
     * @OpenApi\Annotations\Response(response="405", ref="#/definitions/405")
     * @OpenApi\Annotations\Response(response="415", ref="#/definitions/415")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function describeFormAction(Request $request): Response
    {
        return $this->getDescribeFormAction($request);
    }
}
