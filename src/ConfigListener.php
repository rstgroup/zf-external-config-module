<?php


namespace RstGroup\ZfExternalConfigModule;


use Interop\Container\ContainerInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

final class ConfigListener
{
    /** @var ServiceManager */
    private $configServiceManager;

    public function onMergeConfig(ModuleEvent $event)
    {
        $configListener = $event->getConfigListener();
        $config         = $configListener->getMergedConfig(false);

        // init service manager - as it is required to
        $this->initServiceManager($config['rst_group']['external_config']['service_manager']);

        // merge config from each provider
        foreach ($this->getConfigProviders($config['rst_group']['external_config']['providers']) as $configProvider) {
            $config = ArrayUtils::merge($config, $configProvider->getConfig());
        }

        $configListener->setMergedConfig($config);
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer()
    {
        if (!isset($this->configServiceManager)) {
            throw new \RuntimeException("ServiceManager hasn't been initialized!");
        }

        return $this->configServiceManager;
    }

    private function initServiceManager(array $serviceManagerConfiguration)
    {
        $this->configServiceManager = new ServiceManager($serviceManagerConfiguration);
    }

    /**
     * @param array $configProviders
     * @return ConfigProviderInterface[]
     */
    private function getConfigProviders(array $configProviders)
    {
        $container = $this->getContainer();

        return array_map([$container, 'get'], $configProviders);
    }
}
