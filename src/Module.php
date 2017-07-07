<?php


namespace RstGroup\ZfExternalConfigModule;


use RstGroup\ZfExternalConfigModule\Config\ExternalConfigListener;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;

final class Module
{
    public function init(ModuleManager $moduleManager)
    {
        $configListener = new ExternalConfigListener();

        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_MERGE_CONFIG, [
            $configListener, 'onMergeConfig',
        ], -1000);
    }
}
