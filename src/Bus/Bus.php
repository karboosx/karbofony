<?php

namespace Lib\Bus;

use Lib\Kernel\ContainerAwareInterface;
use Lib\Kernel\ContainerAwareTrait;
use ReflectionClass;

class Bus implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private array $listeners = [];

    public function dispatch(object $event): bool
    {
        $reflect = new ReflectionClass($event);

        $eventClass = get_class($event);
        $shortEventClass = $reflect->getShortName();

        if (!isset($this->listeners[$eventClass])) {
            return false;
        }

        $listeners = $this->listeners[$eventClass];

        $processed = false;
        foreach ($listeners as $listenerClass => $methods) {
            $listener = $this->container->get($listenerClass);

            foreach ($methods as $method) {
                $alternativeMethodName = "on$shortEventClass";

                if (method_exists($listener, $method)) {
                    $listener->$method($event);
                    $processed = true;
                } elseif (method_exists($listener, $alternativeMethodName)) {
                    $listener->$alternativeMethodName($event);
                    $processed = true;
                }
            }
        }

        return $processed;
    }

    public function register(string $event, string $listener, ?string $method = null): Bus
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        if (!isset($this->listeners[$event][$listener])) {
            $this->listeners[$event][$listener] = [];
        }

        if (!in_array($method, $this->listeners[$event][$listener], true)) {
            $this->listeners[$event][$listener][] = $method;
        }

        return $this;
    }
}
