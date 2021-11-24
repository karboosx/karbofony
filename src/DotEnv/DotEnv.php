<?php

namespace Lib\DotEnv;

use InvalidArgumentException;

class DotEnv
{
    private string $filename;
    private array $data;
    private array $variables;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function get(string $key, $throwIfNonExist = false)
    {
        if (!isset($this->data)) {
            $this->parse();
        }

        if ($throwIfNonExist && !isset($this->data[$key])) {
            throw new DotEnvException("$key not exist in $this->filename file!");
        }

        return $this->data[$key] ?? null;
    }

    public function parse(): void
    {
        $file = $this->getFie();

        if ($file === false) {
            throw new InvalidArgumentException('File not found!');
        }

        $this->parseFile($file);
    }

    private function getFie()
    {
        return file_get_contents($this->filename);
    }

    private function parseFile($file)
    {
        $lines = explode("\n", $file);

        $this->data = [];
        $this->variables = [];

        $this->set('BASE_DIR', realpath(dirname($this->filename)));
        $this->set('ENV_PATH', realpath($this->filename));

        foreach ($lines as $line) {
            $line = $this->applyVariables($line);
            $this->parseLine($line);
        }
    }

    public function set(string $key, string $value): void
    {
        $parsedValue = $this->parseValue($value);

        $this->data[$key] = $parsedValue;
        $this->variables['%' . $key . '%'] = $parsedValue;
    }

    private function parseValue($value)
    {
        $value = trim($value);

        if ($value === 'true' || $value === 'TRUE') return true;
        if ($value === 'false' || $value === 'FALSE') return false;
        if ($value === 'null' || $value === 'NULL') return null;

        return $value;
    }

    private function applyVariables($line)
    {
        return str_replace(array_keys($this->variables), array_values($this->variables), $line);
    }

    private function parseLine($line)
    {
        $data = explode('=', $line, 2);
        if (count($data) !== 2) {
            return;
        }

        $key = trim($data[0]);
        $value = trim($data[1]);

        if (empty($key) || empty($value)) {
            return;
        }

        $this->set($key, $value);
    }

    public function has(string $key): bool
    {
        if (!isset($this->data)) {
            $this->parse();
        }

        return isset($this->data[$key]);
    }
}
