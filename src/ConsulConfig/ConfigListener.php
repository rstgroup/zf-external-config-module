<?php


namespace RstGroup\ZfConsulConfigModule\ConsulConfig;


use Zend\ModuleManager\ModuleEvent;
use Zend\Stdlib\ArrayUtils;

final class ConfigListener
{
    public function onMergeConfig(ModuleEvent $event)
    {
        $configListener = $event->getConfigListener();
        $config         = $configListener->getMergedConfig();

        $config = ArrayUtils::merge(
            $config,
            $this->getConsulConfig($config['name'], $config['rst_group']['consul_config']['base_url'])
        );

        $configListener->setMergedConfig($config);
    }

    private function getConsulConfig($serviceName, $consulBaseUri)
    {
        return [
            'merged-part' => [
                'name' => $serviceName,
                'uri' => $consulBaseUri,
            ],
        ];
    }
}
