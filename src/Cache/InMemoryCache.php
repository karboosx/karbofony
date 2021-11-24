<?php

namespace Lib\Cache;

class InMemoryCache implements CacheInterface
{
    private array $cache = [];

    public function has(string $key)
    {
        return array_key_exists($key, $this->cache);
    }

    public function get(string $key)
    {
        return $this->cache[$key] ?? null;
    }

    public function put(string $key, $object)
    {
        $this->cache[$key] = $object;
    }

    public function remove(string $key)
    {
        unset($this->cache[$key]);
    }
}