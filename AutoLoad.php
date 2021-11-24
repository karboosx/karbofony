<?php

class AutoLoad
{
    public static AutoLoad $instance;
    private array $classMap = [
        'Lib' => __DIR__ . '/src/'
    ];

    public function __construct()
    {
        self::$instance = $this;

        $this->register();
    }

    private function register()
    {
        spl_autoload_register(function ($class) {
            foreach ($this->classMap as $namespace => $path) {
                if (substr($class, 0, mb_strlen($namespace)) === $namespace) {

                    $file = $this->removeLastSlash($path) . '/' . $this->getFileFromClass($class, $namespace);

                    require_once $file;
                }
            }
        });
    }

    private function removeLastSlash(string $s)
    {
        $lastChar = substr($s, -1, 1);

        if ($lastChar === '/' || $lastChar === '\\') {
            return substr($s, 0, strlen($s) - 1);
        }

        return $s;
    }

    private function getFileFromClass(string $class, string $baseNamespace): string
    {
        $baseNamespaces = explode('\\', $baseNamespace);
        $namespaces = explode('\\', $class);

        $path = [];
        $processBase = true;
        foreach ($namespaces as $i => $namespacePart) {
            if (isset($baseNamespaces[$i]) && $baseNamespaces[$i] === $namespacePart && $processBase) {
                continue;
            }

            $processBase = false;

            $path[] = $namespacePart;
        }

        return implode('/', $path) . '.php';
    }

    public function getClassFromFile(string $file): ?string
    {
        foreach ($this->classMap as $namespace => $path) {
            if (substr($file, 0, strlen($path)) === $path) {
                return $this->convertPathToClass(substr($file, strlen($path)), $namespace);
            }
        }

        return null;
    }

    public function addMapping(string $namespace, string $absolutePath): AutoLoad
    {
        $this->classMap[$namespace] = $absolutePath;

        return $this;
    }

    private function convertPathToClass(string $substr, string $namespace): string
    {
        return $namespace.str_replace(['/','\\'], '\\', substr($substr, 0,-4));
    }
}