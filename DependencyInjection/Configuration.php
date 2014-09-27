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
                    ->info('<info>Route to redirect to on successful login</info>')
                ->end()
                ->scalarNode('mailer_from')
                    ->isRequired()
                    ->info('<info>"from" field for sent emails</info>')
                ->end()
                ->scalarNode('user_class')
                    ->isRequired()
                    ->defaultValue('Tom32i\Bundle\SimpleSecurityBundle\Entity\User')
                    ->info('<info>User class (must extend Tom32i\Bundle\SimpleSecurityBundle\Entity\User)</info>')
                ->end()
                ->scalarNode('login_firewall')
                    ->isRequired()
                    ->info('<info>Firewall to log the user into</info>')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
