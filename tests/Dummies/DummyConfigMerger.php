<?php


namespace RstGroup\ZfExternalConfigModule\Tests\Dummies;


use Zend\Config\Config;
use Zend\ModuleManager\Listener\ConfigMergerInterface;

final class DummyConfigMerger implements ConfigMergerInterface
{
    private $config;

    /**
     * getMergedConfig
     *
     * @param  bool $returnConfigAsObject
     * @return mixed
     */
    public function getMergedConfig($returnConfigAsObject = true)
    {
        return $returnConfigAsObject ?
            new Config($this->config !== null ? $this->config : []) :
            $this->config;
    }

    /**
     * setMergedConfig
     *
     * @param  array $config
     * @return ConfigMergerInterface
     */
    public function setMergedConfig(array $config)
    {
        $this->config = $config;
    }
}
