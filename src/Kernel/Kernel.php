<?php

namespace Lib\Kernel;

use ErrorException;
use Lib\Database\Database;
use Lib\DotEnv\DotEnv;

class Kernel implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __construct()
    {
    }

    public function get(string $id, array $params = [])
    {
        return $this->container->get($id, $params);
    }
}
