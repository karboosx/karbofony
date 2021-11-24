<?php

namespace Lib\Kernel;

use ErrorException;
use Lib\Database\Database;
use Lib\DotEnv\DotEnv;

class Kernel
{
    private Container $container;

    public function __construct()
    {
    }

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function get(string $id, array $params = [])
    {
        return $this->container->get($id, $params);
    }
}
