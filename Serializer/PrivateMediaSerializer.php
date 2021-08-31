<?php

namespace EasyApiBundle\Serializer;

use EasyApiBundle\Entity\MediaUploader\AbstractPrivateMedia;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PrivateMediaSerializer extends AbstractObjectSerializer
{
    private RouterInterface $router;

    /**
     * SerializationListenerFeature constructor.
     * @param ContainerInterface $container
     * @param RouterInterface $router
     */
    public function __construct(ContainerInterface $container, RouterInterface $router)
    {
        parent::__construct($container);
        $this->router = $router;
    }

    /**
     * @param AbstractPrivateMedia $object
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = parent::normalize($object, $format, $context);

        if ($object->getFilename() && !empty($route = $object::getDownloadRouteName())) {
//            $scheme = $this->container->getParameter('router.request_context.scheme');
//            $host = $this->container->getParameter('router.request_context.host');
//            $domain = "{$scheme}://{$host}";
            $data['fileUrl'] = $this->router->generate($route, ['id' => $object->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AbstractPrivateMedia;
    }
}