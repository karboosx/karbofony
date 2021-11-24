<?php

namespace Lib\Database\ORM;

use Lib\Database\Database;

abstract class AutoRepository extends Repository
{
    public function __construct(Database $database)
    {
        parent::__construct($this->getEntityName(), $database);
    }

    abstract function getEntityName(): string;
}