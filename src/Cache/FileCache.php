<?php

namespace Lib\Cache;

class FileCache implements CacheInterface
{
    private string $path;

    public function __construct(string $basePath)
    {
        $this->path = realpath($basePath);
    }

    public function get(string $key)
    {
        if (!$this->has($key)) {
            return null;
        }

        // todo add flock
        $f = fopen($this->getFilename($key), 'r');
        fgets($f);
        $data = stream_get_contents($f);
        fclose($f);

        return unserialize($data);
    }

    public function has(string $key): bool
    {
        if (!file_exists($this->getFilename($key))) {
            return false;
        }

        $f = fopen($this->getFilename($key), 'r');
        $lifetime = fgets($f);
        fclose($f);

        if (time() > (int)$lifetime) {
            $this->remove($key);
            return false;
        }

        return true;
    }

    private function getFilename(string $key): string
    {
        list($dir, $file) = $this->getDirFile($key);

        return $this->path . '/' . $dir . '/' . $file . '.cache';
    }

    private function getDirFile(string $key): array
    {
        $hash = $this->getKeyHash($key);

        $dir = substr($hash, 0, 10);
        $file = substr($hash, 10);
        return array($dir, $file);
    }

    private function getKeyHash(string $key): string
    {
        return sha1(base64_encode($key));
    }

    public function put(string $key, $object, $lifetime = 3600)
    {
        list($dir) = $this->getDirFile($key);

        $dirPath = $this->path . '/' . $dir;

        if (!file_exists($dirPath)) {
            mkdir($dirPath);
        }

        $cacheItem = time()+$lifetime."\n";
        $cacheItem .= serialize($object);

        file_put_contents($this->getFilename($key), $cacheItem);
    }

    public function remove(string $key)
    {
        unlink($this->getFilename($key));
    }
}
