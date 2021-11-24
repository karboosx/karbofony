<?php

namespace Lib\Database\ORM;

use Lib\Database\Database;
use Lib\Kernel\ContainerAwareInterface;
use Lib\Kernel\ContainerAwareTrait;

class Repository implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private string $entityClass;
    private Database $database;

    public function __construct(string $entityClass, Database $database)
    {
        $this->entityClass = $entityClass;
        $this->database = $database;
    }

    public function getByFilter(array $filters): array
    {
        $query = (new QueryBuilder())
            ->select($this->getTableName())
            ->filtersByValue($filters)
            ->getQuery();

        return $this->query($query);
    }

    public function query(Query $query): array
    {
        $rawData = $this->database->queryAll($query->getSql(), $query->getBindings());

        $data = [];

        foreach ($rawData as $rawDatum) {
            $data[] = $this->hydrate($rawDatum);
        }

        return $data;
    }

    private function hydrate($values)
    {
        $reflection = new \ReflectionClass($this->entityClass);

        $entity = $reflection->newInstanceWithoutConstructor();

        foreach ($reflection->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            if (!isset($values[$propertyName])) {
                continue;
            }

            $value = $values[$propertyName];
            if (!$reflectionProperty->isPublic()) {
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($entity, $value);
                $reflectionProperty->setAccessible(false);
            } else {
                $entity->$propertyName = $value;
            }

        }

        return $entity;
    }

    private function dehydrate(object $entity): array
    {
        $data = [];
        $reflection = new \ReflectionClass($this->entityClass);

        foreach ($reflection->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();

            if (!$reflectionProperty->isPublic()) {
                $reflectionProperty->setAccessible(true);
                $data[$propertyName] = $reflectionProperty->getValue($entity);
                $reflectionProperty->setAccessible(false);
            } else {
                $data[$propertyName] = $entity->$propertyName;
            }

        }

        return $data;
    }

    private function getTableName(): string
    {
        $reflection = new \ReflectionClass($this->entityClass);

        $entity = $reflection->newInstanceWithoutConstructor();

        if ($entity instanceof GetTableName) {
            return $entity->getTableName();
        }

        return $this->entityClass;
    }

    public function insert(object $entity): bool
    {
        if (!is_a($entity, $this->entityClass)) {
            throw new ORMException("This entity is not a {$this->entityClass} class!");
        }

        $values = $this->dehydrate($entity);

        $query = (new QueryBuilder())
            ->insert($this->getTableName())
            ->insertValues($values)
            ->getQuery();

        return $this->database->execute($query->getSql(), $query->getBindings());
    }

    public function update(object $entity): bool
    {
        if (!is_a($entity, $this->entityClass)) {
            throw new ORMException("This entity is not a {$this->entityClass} class!");
        }

        if (!($entity instanceof GetPrimaryKey)) {
            throw new ORMException("This entity must implement GetPrimaryKey interface!");
        }

        $values = $this->dehydrate($entity);

        $filters = [];

        foreach ($entity->GetPrimaryKey() as $key) {
            $filters[$key] = $values[$key];
        }

        $query = (new QueryBuilder())
            ->update($this->getTableName())
            ->setValues($values)
            ->filtersByValue($filters)
            ->getQuery();

        return $this->database->execute($query->getSql(), $query->getBindings());
    }
}
