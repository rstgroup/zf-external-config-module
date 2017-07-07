<?php


namespace RstGroup\ZfExternalConfigModule;


use RstGroup\ZfExternalConfigModule\Config\ExternalConfigListener;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;

final class Module implements ConfigProviderInterface
{
    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return [
            'rst_group' => [
                'external_config' => [
                    'providers' => [],
                    'service_manager' => []
                ]
            ]
        ];
    }

    public function init(ModuleManager $moduleManager)
    {
        $configListener = new ExternalConfigListener();

        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_MERGE_CONFIG, [
            $configListener, 'onMergeConfig',
        ], -1000);
    }
}
