<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tom32i_simple_security');

        $rootNode
            ->children()
                ->scalarNode('login_success_redirect')
                    ->isRequired()
                    ->info('<info>route to redirect to on successful login</info>')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
