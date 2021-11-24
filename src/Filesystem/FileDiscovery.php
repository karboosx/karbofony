<?php

namespace Lib\Filesystem;

class FileDiscovery
{
    private $dir;

    public function __construct($dir)
    {
        $this->dir = realpath($dir);
    }

    public function findByExtension(string $extension, $depth = 5): array
    {
        $files = $this->getFilesInDir($this->dir, $depth);

        $out = [];

        foreach ($files as $file) {
            if (substr($file, -strlen($extension)) === $extension) {
                $out[] = $file;
            }
        }

        return $out;
    }

    private function getFilesInDir($dir, $depth = 5): array
    {
        if ($depth === 0) {
            return [];
        }

        $files = [];

        $scan = scandir($dir);

        foreach ($scan as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (is_dir($item)) {
                $filesI = $this->getFilesInDir($dir.'/'.$item, $depth-1);

                array_push($files, ...$filesI);

            }else{
                $files[] = $dir.'/'.$item;
            }
        }

        return $files;
    }
}