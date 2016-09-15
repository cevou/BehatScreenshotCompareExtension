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
    private $adapterFactories = [];

    /**
     * ScreenshotCompareExtension constructor.
     */
    public function __construct()
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
            ->end()
            ->end();

        $adapterNodeBuilder = $builder
            ->children()
            ->arrayNode('adapters')
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->performNoDeepMerging()
            ->children();

        foreach ($this->adapterFactories as $name => $factory) {
            $factoryNode = $adapterNodeBuilder->arrayNode($name)->canBeUnset();
            $factory->addConfiguration($factoryNode);
        }

        $builder
            ->children()
            ->arrayNode('screenshot_config')
            ->children()
            ->arrayNode('breakpoints')
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
            ->integerNode('width')->end()
            ->integerNode('height')->end();

        $builder
            ->children()
            ->arrayNode('sessions')
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
            ->scalarNode('adapter')->defaultValue('default')->end()
            ->arrayNode('crop')
            ->children()
            ->integerNode('left')->defaultValue(0)->min(0)->end()
            ->integerNode('right')->defaultValue(0)->min(0)->end()
            ->integerNode('top')->defaultValue(0)->min(0)->end()
            ->integerNode('bottom')->defaultValue(0)->min(0)->end();
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadContextInitializer(ContainerBuilder $container)
    {
        $definition = new Definition('Cevou\Behat\ScreenshotCompareExtension\Context\Initializer\ScreenshotCompareAwareInitializer', [
            '%screenshot_compare.session_configurations%',
            '%screenshot_compare.parameters%',
        ]);
        $definition->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);
        $container->setDefinition('screenshot_compare.context_initializer', $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     *
     * @throws \LogicException
     */
    private function loadAdaptersAndCreateFilesystem(ContainerBuilder $container, array $config)
    {
        $adapters = [];

        $id = 'gaufrette.screenshot_filesystem';

        foreach ($config['adapters'] as $name => $adapter) {
            $adapter_id = $id . '_' . $name;
            $adapter = $this->createAdapter($name, $adapter, $container, $this->adapterFactories);
            $container->setDefinition($adapter_id, new Definition('Gaufrette\\Filesystem', [new Reference($adapter)]));
            $adapters[$name] = new Reference($adapter_id);
        }

        $sessionConfigurations = [];

        foreach ($config['sessions'] as $name => $session) {
            $sessionConfiguration = [];

            if (!array_key_exists($session['adapter'], $adapters)) {
                throw new \LogicException(sprintf('The adapter \'%s\' is not defined.', $session['adapter']));
            }
            $sessionConfiguration['adapter'] = $adapters[$session['adapter']];
            if (isset($session['crop'])) {
                $sessionConfiguration['crop'] = $session['crop'];
            }
            $sessionConfigurations[$name] = $sessionConfiguration;
        }

        $container->setParameter('screenshot_compare.session_configurations', $sessionConfigurations);
    }

    /**
     * @param $name
     * @param array $config
     * @param ContainerBuilder $container
     * @param AdapterFactory[] $factories
     *
     * @return string
     * @throws \LogicException
     */
    private function createAdapter($name, array $config, ContainerBuilder $container, array $factories)
    {
        $adapter = NULL;
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
