<?php

namespace E7\FeatureFlagsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package E7\FeatureFlagsBundle\DependencyInjection
 * 
 * config example:
 * 
 * e7_feature_flags:
    features:
        feature1: true
        feature2:
            enable: true
            parent: feature1
        feature3: "condition1"
        feature3b: [ "condition4" ]
        
    conditions:
        condition1:
            type: host
            hosts: '*.example.com'
        condition2:
            type: host
            hosts: [ 'foo.example.com', 'www.example.com' ]
        condition3:
            type: ip
            ips: '127.0.0.1'
        condition3b:
            type: ip
            ips: ['127.0.0.1']
        condition3c:
            type: ip
            ips: ['127.0.0.1', '192.168.1.*' ]
        condition4:
            type: Chain
        condition5_bool:
            type: bool
            flag: true
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('e7_feature_flags');

        $rootNode
            ->children()
                ->arrayNode('features')
                    ->arrayPrototype()
                        ->canBeEnabled()
                        ->beforeNormalization()
                            ->ifArray()
                            ->then(function($v) {
                                return (empty($v['type']) && empty($v['parent']) && empty($v['conditions']))
                                    ? ['conditions' => $v] : $v;
                            })
                        ->end() // end: beforeNormalization
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function($v) { return ['conditions' => [$v]]; })
                        ->end() // end: beforeNormalization
                        ->children()
                            ->scalarNode('enable')->end()
                            ->scalarNode('parent')->defaultValue(null)->end()
                            ->arrayNode('conditions')
//                                ->beforeNormalization()
//                                    ->ifString()
//                                    ->then(function($v) { return [$v]; })
//                                ->end() // end: beforeNormalization
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end() // end: features
                ->arrayNode('conditions')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('type')->defaultValue('bool')->end()
                            ->booleanNode('flag')->end() // type: bool
                            ->arrayNode('hosts')
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function($v) { return [$v]; })
                                ->end() // end: beforeNormalization
                                ->scalarPrototype()->end()
                            ->end() // end: hosts
                            ->arrayNode('ips')
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function($v) { return [$v]; })
                                ->end() // end: beforeNormalization
                                ->scalarPrototype()->end()
                            ->end() // type: ipadress
                            ->integerNode('percentage')->end() // type: percent
                        ->end()
                    ->end() // end: arrayPrototype
                ->end() // end: conditions
            ->end();

        return $treeBuilder;
    }
}
