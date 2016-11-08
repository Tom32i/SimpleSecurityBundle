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
        $loader->load('managers.yml');
        $loader->load('subscribers.yml');

        $this->setParameters($container, $config, ['user_class', 'mailer_from', 'redirect_after_authentication']);

        if ($config['login']['enabled']) {
            $this->addRouting($container, 'login');

            $container
                ->getDefinition('tom32i_simple_security.authenticator')
                ->addMethodCall('setFirewall', [$config['login']['firewall']]);
        }

        if ($config['register']['enabled']) {
            $this->addRouting($container, 'register');
        }

        if ($config['password']['enabled']) {
            $this->addRouting($container, 'password');
        }
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

    /**
     * Add routing configuration
     *
     * @param ContainerBuilder $container
     * @param string $name
     */
    private function addRouting(ContainerBuilder $container, $name)
    {
        $container
            ->getDefinition('tom32i_simple_security.routing_loader')
            ->addMethodCall('addResource', [sprintf('@Tom32iSimpleSecurityBundle/Resources/config/routing/%s.yml', $name)]);
    }
}
