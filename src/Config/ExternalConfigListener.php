<?php


namespace RstGroup\ZfExternalConfigModule\Config;


use Interop\Container\ContainerInterface;
use RstGroup\ZfExternalConfigModule\Config\ConfigProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

final class ExternalConfigListener
{
    /** @var ServiceManager */
    private $configServiceManager;

    public function onMergeConfig(ModuleEvent $event)
    {
        $configListener = $event->getConfigListener();
        $appConfig      = $configListener->getMergedConfig(false);

        // get all configuration into the variable..
        $config = $appConfig['rst_group']['external_config'];

        // ..and remove it from app's config - because we don't need it cached
        unset($appConfig['rst_group']['external_config']);

        // init service manager - as it is required to
        $this->initServiceManager($config['service_manager']);

        // merge config from each provider
        foreach ($this->getConfigProviders($config['providers']) as $configProvider) {
            $appConfig = ArrayUtils::merge($appConfig, $configProvider->getConfig());
        }

        $configListener->setMergedConfig($appConfig);
    }

    /**
     * @codeCoverageIgnore
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
