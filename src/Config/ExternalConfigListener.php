<?php


namespace RstGroup\ZfExternalConfigModule\Config;


use Interop\Container\ContainerInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

final class ExternalConfigListener
{
    const SERVICE_APPLICATION_CONFIG = 'config';
    const SERVICE_EXTERNALS_CONFIG = 'external_config';

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
        $this->initServiceManager($config['service_manager'], $appConfig, $config);

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
    public function getInnerContainer()
    {
        if (!isset($this->configServiceManager)) {
            throw new \RuntimeException("ServiceManager hasn't been initialized!");
        }

        return $this->configServiceManager;
    }

    private function initServiceManager(array $serviceManagerConfiguration, array $appConfig, array $externalConfigsConfig)
    {
        $this->configServiceManager = new ServiceManager($serviceManagerConfiguration);
        $this->configServiceManager->setService(self::SERVICE_APPLICATION_CONFIG, $appConfig);
        $this->configServiceManager->setService(self::SERVICE_EXTERNALS_CONFIG, $externalConfigsConfig);
    }

    /**
     * @param array $configProviders
     * @return ConfigProviderInterface[]
     */
    private function getConfigProviders(array $configProviders)
    {
        $container = $this->getInnerContainer();
        $providers = [];

        foreach ($configProviders as $providerService => $isEnabled) {
            // first case - provider given without enabled/disabled value
            if (is_int($providerService) && is_string($isEnabled)) {
                throw new \RuntimeException(sprintf(
                    "Provided '%s' is wrongly defined. It should be passed as array key, with enabled/disabled flag provided as value.",
                    $isEnabled
                ));
            // second case - provider as key, value determines if it's enabled
            } else if ($this->isEnabled($isEnabled)) {
                $providers[] = $container->get($providerService);
            }
        }

        return $providers;
    }

    private function isEnabled($enabled)
    {
        return in_array($enabled, [1, '1', 'on', 'true', true, 'enabled'], true);
    }
}
