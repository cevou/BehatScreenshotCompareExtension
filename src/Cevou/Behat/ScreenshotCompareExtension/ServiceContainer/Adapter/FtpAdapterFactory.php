<?php

namespace Cevou\Behat\ScreenshotCompareExtension\ServiceContainer\Adapter;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FtpAdapterFactory implements AdapterFactory
{

    /**
     * {@inheritdoc}
     */
    function create(ContainerBuilder $container, $id, array $config)
    {
        $container->setDefinition($id, new Definition("Gaufrette\\Adapter\\Ftp", array(
            $config['directory'],
            $config['host'],
            $config
        )));
    }

    /**
     * {@inheritdoc}
     */
    function getKey()
    {
        return 'ftp';
    }

    /**
     * {@inheritdoc}
     */
    function addConfiguration(NodeDefinition $builder)
    {
        $builder
            ->children()
            ->scalarNode('directory')->isRequired()->end()
            ->scalarNode('host')->isRequired()->end()
            ->scalarNode('port')->defaultValue(21)->end()
            ->scalarNode('username')->defaultNull()->end()
            ->scalarNode('password')->defaultNull()->end()
            ->booleanNode('passive')->defaultFalse()->end()
            ->booleanNode('create')->defaultFalse()->end()
            ->booleanNode('ssl')->defaultFalse()->end()
            ->scalarNode('mode')
            ->defaultValue(defined('FTP_ASCII') ? FTP_ASCII : null)
            ->beforeNormalization()
            ->ifString()
            ->then(function ($v) {
                return constant($v);
            })
            ->end()
            ->end();
    }
}
