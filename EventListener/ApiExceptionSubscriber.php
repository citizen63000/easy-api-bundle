<?php

namespace EasyApiBundle\EventListener;

use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Util\ApiProblem;
use Gedmo\Exception\UnexpectedValueException;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $env;

    /**
     * ApiExceptionSubscriber constructor.
     *
     * @param Logger $logger
     * @param $env
     */
    public function __construct(Logger $logger, $env)
    {
        $this->logger = $logger;
        $this->env = $env;
    }

    /**
     * Gets the exception from event, and use an ApiProblem object to set a custom response.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        $apiProblem = ($e instanceof ApiProblemException)
            ? $e->getApiProblem()
            : $this->getApiProblem($e)
        ;

        $response = new JsonResponse(
            $apiProblem->toArray(),
            $apiProblem->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');
        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -1],
        ];
    }

    /**
     * Gets an ApiProblem object from exception.
     *
     * @param \Exception $e
     *
     * @return ApiProblem
     */
    private function getApiProblem(\Exception $e)
    {
        if ($e instanceof HttpException) {
            return new ApiProblem($e->getStatusCode(), $e->getMessage());
        } elseif ($e instanceof AuthenticationException) {
            return new ApiProblem(Response::HTTP_UNAUTHORIZED, $e->getMessage());
        } elseif ($e instanceof AccessDeniedException) {
            return new ApiProblem(Response::HTTP_FORBIDDEN, $e->getMessage());
        } elseif ($e instanceof UnexpectedValueException) {
            return new ApiProblem(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }

        // Unexpected error
        $this->logger->error($e->getMessage(), $e->getTrace());

        $verbose = in_array($this->env, ['dev', 'test']);

        return new ApiProblem(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $verbose ? $e->getMessage() : ApiProblem::UNEXPECTED_ERROR,
            !$verbose
        );
    }
}
