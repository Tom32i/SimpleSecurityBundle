<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.xml');
        $loader->load('forms.xml');
        $loader->load('managers.xml');

        $container
            ->getDefinition('tom32i.simple_security.manager.mail')
            ->replaceArgument(4, $config['mailer_from']);

        $this->setParameters($container, $config, ['user_class', 'login_firewall', 'login_success_redirect']);
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
            $container->setParameter('tom32i_simple_security.' . $key, $value);
        }
    }
}
