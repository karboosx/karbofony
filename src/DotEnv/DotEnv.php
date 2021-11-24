<?php

namespace Lib\DotEnv;

use InvalidArgumentException;

class DotEnv
{
    private string $file;
    private array $data;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function get(string $key)
    {
        if (!isset($this->data)) {
            $this->parse();
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
        return file_get_contents($this->file);
    }

    private function parseFile($file)
    {
        $lines = explode("\n", $file);

        $this->data = [];

        foreach ($lines as $line) {
            $this->parseLine($line);
        }
    }

    private function parseLine($line)
    {
        $data = explode('=', $line, 2);
        $this->data[$data[0]] = $this->parseValue($data[1]);
    }

    private function parseValue($value)
    {
        $value = trim($value);

        if ($value === 'true' || $value === 'TRUE') return true;
        if ($value === 'false' || $value === 'FALSE') return false;
        if ($value === 'null' || $value === 'NULL') return null;

        return $value;
    }
}
