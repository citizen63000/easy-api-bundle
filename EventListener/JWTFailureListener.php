<?php

namespace EasyApiBundle\EventListener;

use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Util\ApiProblem;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\Response;

class JWTFailureListener
{
    /**
     * @param AuthenticationFailureEvent $event
     *
     * @throws ApiProblemException
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        throw new ApiProblemException(new ApiProblem(Response::HTTP_UNAUTHORIZED, ApiProblem::AUTHENTICATION_FAILURE));
    }

    /**
     * @param JWTInvalidEvent $event
     *
     * @throws ApiProblemException
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        throw new ApiProblemException(new ApiProblem(Response::HTTP_UNAUTHORIZED, ApiProblem::JWT_INVALID));
    }

    /**
     * @param JWTNotFoundEvent $event
     *
     * @throws ApiProblemException
     */
    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        throw new ApiProblemException(new ApiProblem(Response::HTTP_UNAUTHORIZED, ApiProblem::JWT_NOT_FOUND));
    }

    /**
     * @param JWTExpiredEvent $event
     *
     * @throws ApiProblemException
     */
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        throw new ApiProblemException(new ApiProblem(Response::HTTP_UNAUTHORIZED, ApiProblem::JWT_EXPIRED));
    }
}
