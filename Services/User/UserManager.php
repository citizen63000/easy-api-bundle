<?php

namespace EasyApiBundle\Services\User;

use EasyApiBundle\EventListener\JWTAuthenticatedListener;
use EasyApiBundle\Services\AbstractService;
use FOS\UserBundle\Model\User;
use Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserManager extends AbstractService
{
    protected AttachRefreshTokenOnSuccessListener $sendTokenService;

    public function __construct(ContainerInterface $container, TokenStorageInterface $tokenStorage, AttachRefreshTokenOnSuccessListener $sendTokenService)
    {
        parent::__construct($container, $tokenStorage);
        $this->sendTokenService = $sendTokenService;
    }

    /**
     * Generate token and refresh token for a user.
     *
     * @param User $user
     *
     * @return array
     *
     * @throws \Exception
     */
    public function generateToken(User $user): array
    {
        // Generate new Token
        $token = $this->get('app.jwt_authentication.jws_provider')->generateTokenByUser($user);

        // Generate Refresh token
        $event = new AuthenticationSuccessEvent(['token' => $token->getToken()], $user, new Response());
        $this->sendTokenService->attachRefreshToken($event);

        return $event->getData();
    }

    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + ['gesdinet.jwtrefreshtoken.send_token' => AttachRefreshTokenOnSuccessListener::class];
    }
}
