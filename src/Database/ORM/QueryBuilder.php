<?php

namespace Lib\Database\ORM;

class QueryBuilder
{
    private array $parts = [];
    private array $bindings = [];

    private array $constraints = [];

    public function getQuery(): Query
    {
        return new Query(implode(' ', $this->parts), $this->bindings);
    }

    public function select(string $table, string $fields = '*'): QueryBuilder
    {
        if ($this->hasConstraint('select')) {
            throw new QueryBuilderException('Already started select!');
        }

        if ($this->hasConstraint('started')) {
            throw new QueryBuilderException('Already started another query!');
        }

        $this->setConstraint('select');
        $this->setConstraint('started');

        $this->parts[] = 'select ' . $fields . ' from ' . $table;

        return $this;
    }

    private function hasConstraint(string $constraint): bool
    {
        return in_array($constraint, $this->constraints);
    }

    private function setConstraint(string $constraint)
    {
        $this->constraints[] = $constraint;
    }

    public function insert(string $table): QueryBuilder
    {
        if ($this->hasConstraint('insert')) {
            throw new QueryBuilderException('Already started insert!');
        }

        if ($this->hasConstraint('started')) {
            throw new QueryBuilderException('Already started another query!');
        }

        $this->setConstraint('insert');
        $this->setConstraint('started');

        $this->parts[] = 'insert into ' . $table;

        return $this;
    }

    public function update(string $table): QueryBuilder
    {
        if ($this->hasConstraint('update')) {
            throw new QueryBuilderException('Already started update!');
        }

        if ($this->hasConstraint('started')) {
            throw new QueryBuilderException('Already started another query!');
        }

        $this->setConstraint('update');
        $this->setConstraint('started');

        $this->parts[] = 'update ' . $table;

        return $this;
    }

    public function insertValues(array $values): QueryBuilder
    {
        $bindings = [];

        foreach ($values as $field => $value) {
            $bindings[] = '?';
            $this->addBinding($value);
        }

        $this->parts[] = '(' . implode(', ', array_keys($values)) . ') values (' . implode(', ', $bindings) . ')';

        return $this;
    }

    public function addBinding($value): QueryBuilder
    {
        $this->bindings[] = $value;

        return $this;
    }

    public function filtersByValue(array $filters): QueryBuilder
    {
        foreach ($filters as $field => $value) {
            $this->whereRaw("$field = ?");
            $this->addBinding($value);
        }

        return $this;
    }

    public function whereRaw(string $whereSql, string $joiner = 'and'): QueryBuilder
    {
        if (!$this->hasConstraint('where')) {
            $this->parts[] = 'where';
            $this->setConstraint('where');
        } else {
            $this->parts[] = 'and';
        }

        $this->parts[] = $whereSql;

        return $this;
    }

    public function setValues(array $values)
    {
        if (!$this->hasConstraint('set')) {
            $this->parts[] = 'set';
            $this->setConstraint('set');
        }

        $parts = [];
        foreach ($values as $field => $value) {
            $this->addBinding($value);

            $parts[] = $field . ' = ?';
        }

        $this->parts[] = implode(', ', $parts);
        return $this;
    }
}