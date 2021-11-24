<?php

namespace Lib\Crawler;

class RegexCrawler
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getParser()
    {
        return new RegexParser($this->getData());
    }

    private function getData()
    {
        return file_get_contents($this->url);
    }
}