<?php

namespace Lib\Database;

class Selector
{

    private string $field;
    private ?string $tag;
    private ?string $app;

    public function __construct(string $field, ?string $tag = null, ?string $app = null)
    {
        $this->field = $field;
        $this->tag = $tag;
        $this->app = $app;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function getApp(): ?string
    {
        return $this->app;
    }
}