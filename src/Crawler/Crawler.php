<?php

namespace Lib\Crawler;

class Crawler
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getParser()
    {
        return new Parser($this->getData());
    }

    private function getData()
    {
        return file_get_contents($this->url);
    }
}