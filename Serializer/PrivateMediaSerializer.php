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

    private string $routerContextScheme;
    private string $routerContextHost;

    /**
     * SerializationListenerFeature constructor.
     * @param ContainerInterface $container
     * @param RouterInterface $router
     * @param string $routerContextScheme
     * @param string $routerContextHost
     */
    public function __construct(ContainerInterface $container, RouterInterface $router, string $routerContextScheme, string $routerContextHost)
    {
        parent::__construct($container);
        $this->router = $router;
        $this->routerContextScheme = $routerContextScheme;
        $this->routerContextHost = $routerContextHost;
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
            $domain = "{$this->routerContextScheme}://{$this->routerContextHost}";
            $data['fileUrl'] = $domain.$this->router->generate($route, ['id' => $object->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
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