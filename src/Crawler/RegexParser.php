<?php

namespace Lib\Crawler;

class RegexParser
{
    private string $data;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public function find(array $regexList)
    {
        $subject = $this->data;
        foreach ($regexList as $regex) {
            unset($matches);
            preg_match_all($regex, $subject, $matches);
            $subject = $matches[0][0];
        }

        return $matches[1];
    }
}