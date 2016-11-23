<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class Tom32iSimpleSecurityExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');

        $this->setParameters($container, $config, ['user_class', 'mailer_from', 'redirect_after_authentication']);
    }

    /**
     * Set parameters
     *
     * @param ContainerBuilder $container
     * @param array $config
     * @param array $keys
     */
    private function setParameters(ContainerBuilder $container, array $config, array $keys)
    {
        $parameters = array_intersect_key($config, array_flip($keys));

        foreach ($parameters as $key => $value) {
            $container->setParameter(sprintf('tom32i_simple_security.parameters.%s', $key), $value);
        }
    }
}
