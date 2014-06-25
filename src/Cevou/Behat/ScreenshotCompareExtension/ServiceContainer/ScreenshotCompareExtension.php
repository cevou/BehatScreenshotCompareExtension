<?php

namespace Cevou\Behat\ScreenshotCompareExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Cevou\Behat\ScreenshotCompareExtension\ServiceContainer\Adapter\AdapterFactory;
use Cevou\Behat\ScreenshotCompareExtension\ServiceContainer\Adapter\FtpAdapterFactory;
use Cevou\Behat\ScreenshotCompareExtension\ServiceContainer\Adapter\LocalAdapterFactory;
use Cevou\Behat\ScreenshotCompareExtension\ServiceContainer\Adapter\SafeLocalAdapterFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ScreenshotCompareExtension implements ExtensionInterface
{

    /**
     * @var AdapterFactory[]
     */
    private $adapterFactories = array();

    function __construct()
    {
        $this->registerAdapterFactory(new LocalAdapterFactory());
        $this->registerAdapterFactory(new SafeLocalAdapterFactory());
        $this->registerAdapterFactory(new FtpAdapterFactory());
    }

    public function registerAdapterFactory(AdapterFactory $adapterFactory)
    {
        $this->adapterFactories[$adapterFactory->getKey()] = $adapterFactory;
    }

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

        $this->loadAdaptersAndCreateFilesystem($container, $config);
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
                ->scalarNode('adapter')->isRequired()->end()
                ->end()
            ->end()
        ->end();

        $adapterNodeBuilder = $builder
            ->children()
            ->arrayNode('adapters')
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->performNoDeepMerging()
            ->children()
        ;

        foreach ($this->adapterFactories as $name => $factory) {
            $factoryNode = $adapterNodeBuilder->arrayNode($name)->canBeUnset();
            $factory->addConfiguration($factoryNode);
        }

    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadContextInitializer(ContainerBuilder $container)
    {
        $definition = new Definition('Cevou\Behat\ScreenshotCompareExtension\Context\Initializer\ScreenshotCompareAwareInitializer', array(
            '%screenshot_compare.filesystem%',
            '%screenshot_compare.parameters%'
        ));
        $definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
        $container->setDefinition('screenshot_compare.context_initializer', $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     * @throws \LogicException
     */
    private function loadAdaptersAndCreateFilesystem(ContainerBuilder $container, array $config)
    {
        $adapters = array();

        foreach ($config['adapters'] as $name => $adapter) {
            $adapters[$name] = $this->createAdapter($name, $adapter, $container, $this->adapterFactories);
        }

        if (!array_key_exists($config['adapter'], $adapters)) {
            throw new \LogicException(sprintf('The adapter \'%s\' is not defined.', $config['adapter']));
        }

        $adapter = $adapters[$config['adapter']];
        $id = 'gaufrette.screenshot_filesystem';

        $container->setDefinition($id, new Definition('Gaufrette\\Filesystem', array(new Reference($adapter))));
        $container->setParameter('screenshot_compare.filesystem', new Reference($id));
    }

    /**
     * @param $name
     * @param array $config
     * @param ContainerBuilder $container
     * @param AdapterFactory[] $factories
     * @return string
     * @throws \LogicException
     */
    private function createAdapter($name, array $config, ContainerBuilder $container, array $factories)
    {
        $adapter = null;
        foreach ($config as $key => $adapter) {
            if (array_key_exists($key, $factories)) {
                $id = sprintf('gaufrette.%s_adapter', $name);
                $factories[$key]->create($container, $id, $adapter);

                return $id;
            }
        }

        throw new \LogicException(sprintf('The adapter \'%s\' is not configured.', $name));
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