<?php

namespace Lib\Database;

use Exception;
use Lib\Database\ORM\Repository;
use Lib\Kernel\ContainerAwareInterface;
use Lib\Kernel\ContainerAwareTrait;
use PDO;

class Database implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private string $dsn;
    private string $user;
    private string $password;
    private string $app;

    private ?PDO $pdo = null;

    public function __construct(string $dsn, string $user, string $password, string $app)
    {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
        $this->app = $app;
    }

    public function queryAll(string $sql, array $bindings = [])
    {
        $this->connect();

        $stm = $this->pdo->prepare($sql);
        $test = $stm->execute($bindings);

        if ($test === false) {
            throw new Exception("Error on query: " . implode(' ', $stm->errorInfo()));
        }

        return $stm->fetchAll();
    }

    public function execute(string $sql, array $bindings = [])
    {
        $this->connect();

        $stm = $this->pdo->prepare($sql);
        $test = $stm->execute($bindings);

        if ($test === false) {
            throw new Exception("Error on query: " . implode(' ', $stm->errorInfo()));
        }

        return $stm->rowCount();
    }

    public function getApp(): string
    {
        return $this->app;
    }

    public function getPdo(): PDO
    {
        $this->connect();

        return $this->pdo;
    }

    public function connect()
    {
        if ($this->pdo !== null) {
            return;
        }

        $this->pdo = new PDO($this->dsn, $this->user, $this->password);
    }

    public function getRepository(string $entityClass): Repository
    {
        return $this->container->get(Repository::class, [
            'entityClass' => $entityClass
        ]);
    }
}
