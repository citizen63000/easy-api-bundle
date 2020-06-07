<?php

namespace EasyApiBundle\Services\User;

use FOS\UserBundle\Model\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use EasyApiBundle\Util\AbstractService;
use Symfony\Component\HttpFoundation\Response;

class UserManager extends AbstractService
{
    /**
     * Generate token and refresh token for a user.
     *
     * @param $user
     *
     * @return array
     *
     * @throws \Exception
     */
    public function generateToken(User $user)
    {
        // Generate new Token
        $jwtManager = $this->get('app.jwt_authentication.jws_provider');
        $token = $jwtManager->generateTokenByUser($user);

        // Generate Refresh token
        $event = new AuthenticationSuccessEvent(['token' => $token->getToken()], $user, new Response());
        $refreshToken = $this->get('gesdinet.jwtrefreshtoken.refresh_token_manager')->getLastFromUsername($user->getUsername())->getRefreshToken();
        $this->get('gesdinet.jwtrefreshtoken.send_token')->attachRefreshToken($event);
        $this->get('app.event.jwt_authentication_success_listener')->onAuthenticationSuccessResponse($event);

        return $event->getData();
    }


}
