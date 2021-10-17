<?php

namespace EasyApiBundle\EventListener;

use EasyApiBundle\Services\User\Tracking;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class JWTAuthenticatedListener
{
    /**
     * @var EntityManager
     */
    protected EntityManager $em;

    /**
     * @var RequestContext
     */
    protected RequestContext $requestContext;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var string
     */
    protected string $jwtTokenTTL;

    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var EncoderFactoryInterface
     */
    protected EncoderFactoryInterface $encoderFactory;

    /**
     * JWTAuthenticatedListener constructor.
     *
     * @param EntityManager        $em
     * @param RequestContext       $requestContext
     * @param RequestStack         $requestStack
     * @param $jwtTokenTTL
     * @param Container               $container
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(EntityManager $em, RequestContext $requestContext, RequestStack $requestStack, $jwtTokenTTL, Container $container, EncoderFactoryInterface $encoderFactory)
    {
        $this->em = $em;
        $this->requestContext = $requestContext;
        $this->requestStack = $requestStack;
        $this->jwtTokenTTL = $jwtTokenTTL;
        $this->container = $container;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @param JWTAuthenticatedEvent $event
     *
     * @throws \Exception
     */
    public function onJWTAuthenticated(JWTAuthenticatedEvent $event): void
    {
        if ($this->container->getParameter(Tracking::TRACKING_ENABLE_PARAMETER)) {
            $this->container->get('app.user.tracking')->updateLastAction(
                $event->getToken()->getUser(),
                $this->requestStack->getCurrentRequest(),
                $event->getToken()->getCredentials()
            );
        }
    }
}
