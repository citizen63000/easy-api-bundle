<?php


namespace EasyApiBundle\Controller\User;

use FOS\UserBundle\Model\User;
use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Util\ApiProblem;
use FOS\RestBundle\View\View;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use EasyApiBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

class UserAuthenticationController extends AbstractApiController
{
    /**
     * Provides a refresh token management.
     *
     * @SWG\Tag(name="Authentication")
     *
     * @SWG\Parameter(ref="#/parameters/refresh_token"),
     *
     * @SWG\Response(response="200", ref="#/definitions/security_auth_refresh"),
     * @SWG\Response(response="204", ref="#/definitions/204"),
     * @SWG\Response(response="400", ref="#/definitions/400"),
     * @SWG\Response(response="401", ref="#/definitions/401"),
     * @SWG\Response(response="405", ref="#/definitions/405")
     *
     * @Rest\View()
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws \Exception
     */
    public function refreshTokenAction(Request $request)
    {
        /** @var RefreshToken $refreshToken */
        $refreshToken = $this->getRepository(RefreshToken::class)->findOneByRefreshToken($request->get('refreshToken'));
        if (null === $refreshToken) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_NOT_FOUND, sprintf(ApiProblem::ENTITY_NOT_FOUND, 'token'), 'user')
            );
        }

        $user = $this->getRepository(User::class)->findOneByUsername($refreshToken->getUsername());
        if (null === $user) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_NOT_FOUND, sprintf(ApiProblem::ENTITY_NOT_FOUND, 'user'), 'user')
            );
        }

        return $this->getUserManager()->generateToken($user);
    }

    /**
     * POST - User logout.
     *
     * @SWG\Tag(name="Authentication")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successful operation"
     * ),
     * @SWG\Response(response="401", ref="#/definitions/401"),
     * @SWG\Response(response="403", ref="#/definitions/403"),
     * @SWG\Response(response="415", ref="#/definitions/415"),
     * @SWG\Response(response="422", ref="#/definitions/422")
     *
     * @return View
     */
    public function logoutAction()
    {
        $this->get('security.token_storage')->setToken();
        $this->get('session')->invalidate();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}