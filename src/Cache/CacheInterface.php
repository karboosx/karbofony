<?php

namespace Lib\Cache;

interface CacheInterface
{
    public function has(string $key);

    public function get(string $key);

    public function put(string $key, $object);

    public function remove(string $key);
}