<?php

namespace Lib\Bus;

use Lib\Cache\CacheInterface;
use Lib\Filesystem\FileDiscovery;

class SubscriberDiscovery
{
    const BUS_SUBSCRIBERS_CACHE_KEY = 'bus.subscribers';
    private FileDiscovery $fileDiscovery;
    private CacheInterface $cache;
    private Bus $bus;

    public function __construct(FileDiscovery $fileDiscovery, CacheInterface $cache, Bus $bus)
    {
        $this->fileDiscovery = $fileDiscovery;
        $this->cache = $cache;
        $this->bus = $bus;
    }

    private function findClasses(): array
    {
        $files = $this->fileDiscovery->findByExtension('.php');

        $res = [];

        foreach ($files as $file) {
            $class = \AutoLoad::$instance->getClassFromFile($file);

            if ($class === null) {
                continue;
            }

            if (!class_exists($class)) {
                continue;
            }

            $res[] = $class;
        }

        return $res;
    }


    public function registerSubscribers()
    {
        if ($this->cache->has(self::BUS_SUBSCRIBERS_CACHE_KEY)) {
            $subscribers = $this->cache->get(self::BUS_SUBSCRIBERS_CACHE_KEY);
        }else {
            $subscribers = $this->findSubscribers();
            $this->cache->put(self::BUS_SUBSCRIBERS_CACHE_KEY, $subscribers);
        }

        foreach ($subscribers as $subscriber) {
            $this->bus->register($subscriber[0], $subscriber[1], $subscriber[2]);
        }
    }

    private function findSubscribers(): array
    {
        $subscribers = [];
        $classes = $this->findClasses();

        foreach ($classes as $class) {
            $reflect = new \ReflectionClass($class);

            if ($reflect->implementsInterface(RegisterSubscriber::class)) {
                /** @var RegisterSubscriber $object */
                $object = $reflect->newInstanceWithoutConstructor();

                foreach ($object->getEvents() as $event => $method) {
                    $subscribers[] = [$event, $class, $method];

                }
            }
        }

        return $subscribers;
    }
}