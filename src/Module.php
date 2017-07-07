<?php


namespace RstGroup\ZfExternalConfigModule;


use RstGroup\ZfExternalConfigModule\ConfigListener;
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
                'consul_config' => [
                    'base_url' => 'http://localhost:8500'
                ]
            ]
        ];
    }

    public function init(ModuleManager $moduleManager)
    {
        $configListener = new ConfigListener();

        $eventManager = $moduleManager->getEventManager();

        $eventManager->attach(ModuleEvent::EVENT_MERGE_CONFIG, [
            $configListener, 'onMergeConfig'
        ], -1000);
    }
}
