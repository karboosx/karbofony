<?php

namespace Lib\Database\ORM;

interface GetPrimaryKey
{
    /** @return string[] */
    public function GetPrimaryKey(): array;
}