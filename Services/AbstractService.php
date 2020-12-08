<?php

namespace EasyApiBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use EasyApiBundle\Entity\User\AbstractUser;
use EasyApiBundle\Util\CoreUtilsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractService
{
    use CoreUtilsTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var AbstractUser|null
     */
    protected $user;

    /**
     * DocumentGenerator constructor.
     *
     * @todo: replace by ServiceLocator
     * see https://symfony.com/doc/current/service_container/service_subscribers_locators.html
     *
     * @param ContainerInterface     $container
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(ContainerInterface $container, TokenStorageInterface $tokenStorage = null)
    {
        $this->container = $container;
        $this->user = ($tokenStorage && $token = $tokenStorage->getToken()) ? $token->getUser() : null;
    }

    /**
     * @param $id
     * @param int $invalidBehavior
     *
     * @return object
     *
     * @throws \Exception
     */
    protected function get($id, $invalidBehavior = Container::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->container->get($id, $invalidBehavior);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry|null
     */
    protected function getDoctrine()
    {
        try {
            return $this->container->get('doctrine');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return AbstractUser|null
     */
    protected function getUser()
    {
        return $this->user;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     *
     * @final since version 3.4
     *
     * @throws \Exception
     */
    protected function render(string $view, array $parameters = [], Response $response = null)
    {
        if ($this->container->has('templating')) {
            $content = $this->container->get('templating')->render($view, $parameters);
        } elseif ($this->container->has('twig')) {
            $content = $this->container->get('twig')->render($view, $parameters);
        } else {
            throw new \LogicException('You can not use the "render" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }

        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type The fully qualified class name of the form type
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     *
     * @return FormInterface
     */
    protected function createForm(string $type, $data = null, array $options = [])
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }
}
