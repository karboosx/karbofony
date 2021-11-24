<?php

namespace Lib\Kernel;

use Lib\Bus\Bus;
use Lib\Bus\SubscriberDiscovery;
use Lib\Cache\CacheInterface;
use Lib\Cache\FileCache;
use Lib\Cache\InMemoryCache;
use Lib\Database\Database;
use Lib\DotEnv\DotEnv;
use Lib\Filesystem\FileDiscovery;

class Factory
{
    public static function fromDotEnv(string $file): Kernel
    {
        $container = new Container();


        /** @var DotEnv $dotEnv */
        $dotEnv = $container->get(DotEnv::class, [
            'filename' => $file
        ]);

        $dotEnv->parse();

        $debug = $dotEnv->get('APP_DEBUG') ?? false;

        if ($debug) {
            $container->setAlias(CacheInterface::class, InMemoryCache::class);
        } else {

            $container->setAlias(CacheInterface::class, FileCache::class);
        }

        $container->setConcrete(CacheInterface::class, $container->get(CacheInterface::class));

        /** @var Database $database */
        $database = $container->get(Database::class, [
            'dsn' => $dotEnv->get('DB_DSN', true),
            'user' => $dotEnv->get('DB_USER', true),
            'password' => $dotEnv->get('DB_PASSWORD', true),
            'app' => $dotEnv->get('APP_NAME', true),
        ]);

        if ($dotEnv->has('CACHE_DIR')) {

            $cache = $container->get(CacheInterface::class, [
                'basePath' => $dotEnv->get('CACHE_DIR')
            ]);

            $container->setConcreteClass($cache);
        }

        self::setupBus($dotEnv, $container);

        $container->setConcreteClass($dotEnv);
        $container->setConcreteClass($database);
        $kernel = $container->get(Kernel::class);

        $container->setConcreteClass($kernel);

        return $kernel;
    }

    private static function setupBus(DotEnv $dotEnv, Container $container)
    {
        /** @var Bus $bus */
        $bus = $container->get(Bus::class);
        $container->setConcreteClass($bus);

        /** @var FileDiscovery $fileDiscovery */
        $fileDiscovery = $container->get(FileDiscovery::class, [
            'dir' => $dotEnv->get('SUBSCRIBERS_DIR') ?? $dotEnv->get('BASE_DIR', true)
        ]);

        /** @var SubscriberDiscovery $subscriberDiscovery */
        $subscriberDiscovery = $container->get(SubscriberDiscovery::class, [
            'fileDiscovery' => $fileDiscovery
        ]);

        $subscriberDiscovery->registerSubscribers();
    }
}
