<?php


namespace EasyApiBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class ApiKernel extends Kernel
{
    /**
     * @return array|iterable|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles()
    {
        $bundles = [
            new \EasyApiBundle\EasyApiBundle(),
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
            new \Gesdinet\JWTRefreshTokenBundle\GesdinetJWTRefreshTokenBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\AopBundle\JMSAopBundle(),
            new \JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new \JMS\DiExtraBundle\JMSDiExtraBundle(),
            new \Nelmio\CorsBundle\NelmioCorsBundle(),
            new \FOS\UserBundle\FOSUserBundle(),
            new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new \FOS\HttpCacheBundle\FOSHttpCacheBundle(),
            new \Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new \Vich\UploaderBundle\VichUploaderBundle(),
            new \Oneup\FlysystemBundle\OneupFlysystemBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return dirname($this->getRootDir()).'/var/cache/'.$this->getEnvironment();
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return dirname($this->getRootDir()).'/var/logs';
    }

    /**
     * @param LoaderInterface $loader
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->setParameter('container.autowiring.strict_mode', true);
            $container->setParameter('container.dumper.inline_class_loader', true);

            $container->addObjectResource($this);
        });
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}