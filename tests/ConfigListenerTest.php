<?php


namespace RstGroup\ZfExternalConfigModule\Tests;


use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use RstGroup\ZfExternalConfigModule\Config\ExternalConfigListener;
use RstGroup\ZfExternalConfigModule\Config\ConfigProviderInterface;
use RstGroup\ZfExternalConfigModule\Module;
use RstGroup\ZfExternalConfigModule\Tests\Dummies\DummyConfigMerger;
use RstGroup\ZfExternalConfigModule\Tests\Dummies\DummyProviderWithInnerContainer;
use RstGroup\ZfExternalConfigModule\Tests\Dummies\DummyProviderWithInnerContainerFactory;
use RstGroup\ZfExternalConfigModule\Tests\Dummies\TestEventManager;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;


class ConfigListenerTest extends TestCase
{
    public function testItAttachesToModuleEventMergeConfig()
    {
        // given: event manager
        $eventManager = new TestEventManager();

        // given: module manager
        $moduleManager = $this->getMockBuilder(ModuleManager::class)->disableOriginalConstructor()->getMock();
        $moduleManager->method('getEventManager')->willReturn($eventManager);

        // given: module
        $module = new Module();

        // assume: no listener is present
        $this->assertSame(0, $eventManager->countEventListeners(ModuleEvent::EVENT_MERGE_CONFIG));

        // when: module is initialized..
        $module->init($moduleManager);

        // then: the listener is added
        $this->assertSame(1, $eventManager->countEventListeners(ModuleEvent::EVENT_MERGE_CONFIG));
    }

    public function testItMergesConfigFromGivenProviders()
    {
        // given: external config provider
        $configProvider = $this->getMockBuilder(ConfigProviderInterface::class)->getMock();
        $configProvider->method('getConfig')->willReturn(
            [
                'merged-config' => [
                    'x' => 'y',
                ],
            ]
        );

        // given: application's merged config with information about external config provider..
        $appsMergedConfig = [
            'name'      => 'application',
            'rst_group' => [
                'external_config' => [
                    'providers'       => [
                        'DummyProvider',
                    ],
                    'service_manager' => [
                        'services' => [
                            'DummyProvider' => $configProvider,
                        ],
                    ],
                ],
            ],
        ];

        // given: Module Event with merged config
        $zendConfigListener = new DummyConfigMerger();
        $zendConfigListener->setMergedConfig($appsMergedConfig);

        $moduleEvent = new ModuleEvent(ModuleEvent::EVENT_MERGE_CONFIG);
        $moduleEvent->setConfigListener($zendConfigListener);

        // given:
        $configListener = new ExternalConfigListener();

        // when:
        $configListener->onMergeConfig($moduleEvent);

        // then:
        $this->assertEquals(
            [
                'name'          => 'application',
                'rst_group'     => [],
                'merged-config' => [
                    'x' => 'y',
                ],
            ],
            $moduleEvent->getConfigListener()->getMergedConfig(false)
        );
    }

    public function testInnerServiceManagerHasAppsAndExternalConfigurationAvailable()
    {
        // given: var to store config from inner container
        $configFromInnerContainer = null;

        // given: application's merged config with information about external config provider..
        $appsMergedConfig = [
            'name'      => 'application',
            'rst_group' => [
                'external_config' => [
                    'providers'       => [],
                    'service_manager' => [],
                ],
            ],
        ];

        // given: Module Event with merged config
        $zendConfigListener = new DummyConfigMerger();
        $zendConfigListener->setMergedConfig($appsMergedConfig);

        $moduleEvent = new ModuleEvent(ModuleEvent::EVENT_MERGE_CONFIG);
        $moduleEvent->setConfigListener($zendConfigListener);

        // given:
        $configListener = new ExternalConfigListener();

        // when:
        $configListener->onMergeConfig($moduleEvent);

        // then:
        $this->assertEquals(
            [
                'name'      => 'application',
                'rst_group' => [],
            ],
            $configListener->getInnerContainer()->get(ExternalConfigListener::SERVICE_APPLICATION_CONFIG)
        );
        $this->assertEquals(
            [
                'providers'       => [],
                'service_manager' => [],
            ],
            $configListener->getInnerContainer()->get(ExternalConfigListener::SERVICE_EXTERNALS_CONFIG)
        );
    }
}
