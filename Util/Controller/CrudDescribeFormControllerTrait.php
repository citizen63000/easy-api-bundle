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
     *     @Nelmio\ApiDocBundle\Annotation\Model(type="EasyApiBundle\Util\Forms\SerializedForm", groups={"public"})
     * )
     *
     * @OpenApi\Annotations\Response(response="404", description="Entity not found"),
     * @OpenApi\Annotations\Response(response="405", description="Method not allowed"),
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
