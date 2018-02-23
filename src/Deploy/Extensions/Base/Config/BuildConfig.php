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
                        ->scalarNode('frontend')->isRequired()->defaultNull()->end()
                        ->scalarNode('backend')->isRequired()->defaultNull()->end()
                        ->scalarNode('path')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('target')
                    ->children()
                        ->arrayNode('web')
                            ->children()
                                ->scalarNode('host')->end()
                                ->scalarNode('path')->end()
                                ->booleanNode('https')->defaultTrue()->end()
                                ->arrayNode('server')
                                    ->beforeNormalization()
                                    ->ifString()
                                    ->then(function($v) {
                                        return array($v);
                                    })
                                    ->end()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('admin')
                            ->children()
                                ->scalarNode('host')->end()
                                ->scalarNode('path')->end()
                                ->booleanNode('https')->defaultTrue()->end()
                                ->arrayNode('server')
                                    ->beforeNormalization()
                                    ->ifString()
                                    ->then(function($v) {
                                        return array($v);
                                    })
                                    ->end()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('service')
                            ->children()
                                ->scalarNode('host')->end()
                                ->scalarNode('path')->end()
                                ->booleanNode('https')->defaultTrue()->end()
                                ->arrayNode('server')
                                    ->beforeNormalization()
                                    ->ifString()
                                        ->then(function($v) {
                                            return array($v);
                                        })
                                    ->end()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('remote')
                    ->children()
                        ->scalarNode('user')->isRequired()->end()
                        ->scalarNode('key')->defaultNull()->end()
                        ->arrayNode('pre')
                            ->beforeNormalization()
                            ->ifString()
                            ->then(function($v) {
                                return array($v);
                            })
                            ->end()
                            ->prototype('array')->end()
                        ->end()
                        ->arrayNode('post')
                            ->beforeNormalization()
                            ->ifString()
                            ->then(function($v) {
                                return array($v);
                            })
                            ->end()
                            ->prototype('array')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
