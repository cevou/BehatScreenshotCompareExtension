<?php

namespace Cevou\Behat\ScreenshotCompareExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ScreenshotCompareExtension implements ExtensionInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'screenshot_compare';
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter('screenshot_compare.parameters', $config);
        $container->setParameter('screenshot_compare.screenshot_dir', $config['screenshot_dir']);
        $container->setParameter('screenshot_compare.target_dir', $config['target_dir']);

        $this->loadContextInitializer($container);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('screenshot_dir')->defaultValue('%paths.base%/features/screenshots')->end()
            ->scalarNode('target_dir')->defaultValue('%paths.base%/compared_screens')->end()
            ->end()
            ->end();
    }

    private function loadContextInitializer(ContainerBuilder $container)
    {
        $definition = new Definition('Cevou\Behat\ScreenshotCompareExtension\Context\Initializer\ScreenshotCompareAwareInitializer', array(
            '%screenshot_compare.parameters%',
        ));
        $definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
        $container->setDefinition('screenshot_compare.context_initializer', $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

}