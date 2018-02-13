<?php

namespace Deploy\Extensions\Base\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class BuildConfig implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('build');
        $rootNode
            ->children()
                ->arrayNode('source')
                    ->children()
                        ->scalarNode('repo')->isRequired()->end()
                        ->scalarNode('revision')->isRequired()->end()
                        ->scalarNode('path')->isRequired()->end()
                        ->scalarNode('backend')->isRequired()->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
