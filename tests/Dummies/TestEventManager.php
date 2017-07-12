<?php


namespace RstGroup\ZfExternalConfigModule\Tests\Dummies;


use Zend\EventManager\EventManager;

final class TestEventManager extends EventManager
{
    public function countEventListeners($eventName)
    {
        return isset($this->events[$eventName]) ?
            count($this->events[$eventName]) :
            0;
    }
}
