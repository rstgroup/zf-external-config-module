<?php


namespace RstGroup\ZfConsulConfigModule;


use RstGroup\ZfConsulConfigModule\ConsulConfig\ConfigListener;
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
            'listeners' => [

            ]
        ];
    }

    public function init(ModuleManager $moduleManager)
    {
        $configListener = new ConfigListener();

        $eventManager = $moduleManager->getEventManager();

        $eventManager->attach(ModuleEvent::EVENT_MERGE_CONFIG, [
            $configListener, 'onMergeConfig'
        ], 1001);
    }
}
