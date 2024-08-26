<?php

namespace EasyApiBundle\Serializer;

use EasyApiBundle\Entity\MediaUploader\AbstractPrivateMedia;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PrivateMediaSerializer extends AbstractObjectSerializer
{
    private RouterInterface $router;

    private ParameterBagInterface $parameterBag;

    public function __construct(ContainerInterface $container, RouterInterface $router, ParameterBagInterface $parameterBag)
    {
        parent::__construct($container);
        $this->router = $router;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
    {
        $data = parent::normalize($object, $format, $context);

        if ($object->getFilename() && !empty($route = $object::getDownloadRouteName())) {
            $domain = "{$this->parameterBag->get('router.request_context.scheme')}://{$this->parameterBag->get('router.request_context.host')}";
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
