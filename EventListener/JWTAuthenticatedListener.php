<?php

namespace EasyApiBundle\EventListener;

use EasyApiBundle\Services\User\Tracking;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTAuthenticatedListener
{

    protected RequestStack $requestStack;
    protected Tracking $tracking;
    protected ParameterBagInterface $params;

    public function __construct(Tracking $tracking, RequestStack $requestStack, ParameterBagInterface $params)
    {
        $this->requestStack = $requestStack;
        $this->tracking = $tracking;
        $this->params = $params;
    }

    /**
     * @param JWTAuthenticatedEvent $event
     *
     * @throws \Exception
     */
    public function onJWTAuthenticated(JWTAuthenticatedEvent $event): void
    {
        if ($this->params->get(Tracking::TRACKING_ENABLE_PARAMETER)) {
            $this->tracking->updateLastAction(
                $event->getToken()->getUser(),
                $this->requestStack->getCurrentRequest(),
                $event->getToken()->getCredentials()
            );
        }
    }
}
