<?php

namespace Lib\Kernel;

use Lib\Database\Database;
use Lib\DotEnv\DotEnv;

class Factory
{
    public static function fromDotEnv(string $file): Kernel
    {
        $container = new Container();

        $dotEnv = $container->get(DotEnv::class, [
            'file' => $file
        ]);

        $dotEnv->parse();

        $database = $container->get(Database::class, [
            'dsn' => $dotEnv->get('DB_DSN'),
            'user' => $dotEnv->get('DB_USER'),
            'password' => $dotEnv->get('DB_PASSWORD'),
            'app' => $dotEnv->get('APP_NAME')
        ]);

        $kernel = new Kernel();

        $container->addConcreteClass($dotEnv);
        $container->addConcreteClass($database);
        $container->addConcreteClass($kernel);
        $kernel->setContainer($container);

        return $kernel;
    }
}
