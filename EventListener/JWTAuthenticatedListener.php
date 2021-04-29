<?php

namespace EasyApiBundle\EventListener;

use EasyApiBundle\Services\User\Tracking;
use EasyApiBundle\Entity\User\AbstractUser as User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class JWTAuthenticatedListener
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var RequestContext
     */
    protected $requestContext;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $jwtTokenTTL;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var EncoderFactory
     */
    protected $encoderFactory;

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
     * @param AuthenticationSuccessEvent $event
     *
     * @throws OptimisticLockException
     * @throws ORMException|\Exception
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        // deny multiple connections on the same login
//        $manager = $this->container->get('gesdinet.jwtrefreshtoken.refresh_token_manager');
//        $refreshToken = $manager->getLastFromUsername($user->getUsername());
//        if ($refreshToken) {
//            $manager->delete($refreshToken);
//        }

        if($this->container->getParameter(Tracking::TRACKING_ENABLE_PARAMETER)) {
            $this->container->get('app.user.tracking')->logConnection(
                $user,
                $this->requestStack->getCurrentRequest(),
                $event->getData()['token']
            );
        }
    }

    /**
     * @param JWTAuthenticatedEvent $event
     *
     * @throws ORMException
     * @throws OptimisticLockException|\Exception
     */
    public function onJWTAuthenticated(JWTAuthenticatedEvent $event): void
    {
        $payload = $event->getPayload();
        $payload['displayName'] = $event->getToken()->getUser()->__toString();
        $event->setPayload($payload);

        if($this->container->getParameter(Tracking::TRACKING_ENABLE_PARAMETER)) {
            $this->container->get('app.user.tracking')->updateLastAction(
                $event->getToken()->getUser(),
                $this->requestStack->getCurrentRequest(),
                $event->getToken()->getCredentials()
            );
        }

    }
}
