<?php

namespace EasyApiBundle\Util\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Annotations as OA;

trait CrudDescribeFormControllerTrait
{
    /**
     * Describe fields of form.
     *
     *
     * @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Nelmio\ApiDocBundle\Annotation\Model(type="EasyApiBundle\Util\Forms\SerializedForm", groups={"public"})
     * )
     * @OA\Response(response="404", description="Entity not found"),
     * @OA\Response(response="405", description="Method not allowed"),
     */
    #[Route(path: '/describeForm', name: '_describe_form', methods: ['GET'])]
    public function describeForm(Request $request): Response
    {
        return $this->doGetDescribeForm($request);
    }
}
