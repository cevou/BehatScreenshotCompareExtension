<?php

namespace Cevou\Behat\ScreenshotCompareExtension\ServiceContainer\Adapter;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LocalAdapterFactory implements AdapterFactory
{

    /**
     * {@inheritdoc}
     */
    function create(ContainerBuilder $container, $id, array $config)
    {
        $container->setDefinition($id, new Definition("Gaufrette\\Adapter\\Local", array(
            $config['directory'],
            $config['create']
        )));
    }

    /**
     * {@inheritdoc}
     */
    function getKey()
    {
        return 'local';
    }

    /**
     * {@inheritdoc}
     */
    function addConfiguration(NodeDefinition $builder)
    {
        $builder
            ->children()
            ->scalarNode('directory')->isRequired()->end()
            ->booleanNode('create')->defaultTrue()->end()
            ->end();
    }
}
