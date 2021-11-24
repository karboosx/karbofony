<?php

namespace Lib\Database;

use PDO;
use PDOStatement;

class BasicDataAccess
{
    private const QUERY_WITHOUT_TAG = 'queryWithoutTag';
    private const QUERY_WITH_TAG = 'queryWithTag';
    private const UPDATE_WITHOUT_TAG = 'updateWithoutTag';
    private const UPDATE_WITH_TAG = 'updateWithTag';
    
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @var PDOStatement[]
     */
    private array $queries;

    public function getString(Selector $selector)
    {
        return $this->parseData($selector, 'string');
    }

    private function parseData(Selector $selector, string $type)
    {
        $value = $this->getValue($selector, $type);

        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'int':
                $value = (int)$value;
                break;
            case 'string':
                $value = (string)$value;
                break;
        }

        return $value;
    }

    public function setUp()
    {
        $this->database->connect();
        
        if (isset($this->queries)) {
            return;
        }
        $this->queries = [];

        $this->queries[self::QUERY_WITHOUT_TAG] = $this->database->getPdo()->prepare('select value from data where app = :app and field = :field and type = :type limit 1');
        $this->queries[self::QUERY_WITH_TAG] = $this->database->getPdo()->prepare('select value from data where app = :app and field = :field and tag = :tag and type = :type limit 1');

        $this->queries[self::UPDATE_WITHOUT_TAG] = $this->database->getPdo()->prepare('INSERT INTO data (app, field, type, value, tag) VALUES (:app, :field, :type, :value, null) on duplicate key update value = :value2');
        $this->queries[self::UPDATE_WITH_TAG] = $this->database->getPdo()->prepare('INSERT INTO data (app, field, type, tag, value) VALUES (:app, :field, :type, :tag, :value) on duplicate key update value = :value2');
    }

    private function getValue(Selector $selector, string $type)
    {
        $this->setUp();

        $app = $selector->getApp() ?? $this->database->getApp();

        if ($selector->getTag() === null) {
            $this->queries[self::QUERY_WITHOUT_TAG]->execute([
                'app' => $app,
                'field' => $selector->getField(),
                'type' => $type,
            ]);

            $fetchedData = $this->queries[self::QUERY_WITHOUT_TAG]->fetch(PDO::FETCH_ASSOC);

        } else {
            $this->queries[self::QUERY_WITH_TAG]->execute([
                'app' => $app,
                'field' => $selector->getField(),
                'tag' => $selector->getTag(),
                'type' => $type,
            ]);

            $fetchedData = $this->queries[self::QUERY_WITH_TAG]->fetch(PDO::FETCH_ASSOC);
        }

        if ($fetchedData === false) {
            return null;
        }

        return $fetchedData['value'];
    }

    /** @noinspection PhpUnused */

    public function getInt(Selector $selector)
    {
        return $this->parseData($selector, 'int');
    }

    /** @noinspection PhpUnused */

    public function setString(Selector $selector, string $value)
    {
        $this->setValue($selector, 'string', $value);
    }

    /** @noinspection PhpUnused */

    private function setValue(Selector $selector, string $type, $value)
    {
        $this->setUp();

        $app = $selector->getApp() ?? $this->database->getApp();

        if ($selector->getTag() === null) {
            $this->queries[self::UPDATE_WITHOUT_TAG]->execute([
                'app' => $app,
                'field' => $selector->getField(),
                'type' => $type,
                'value' => $value,
                'value2' => $value
            ]);

        } else {
            $this->queries[self::UPDATE_WITH_TAG]->execute([
                'app' => $app,
                'field' => $selector->getField(),
                'tag' => $selector->getTag(),
                'type' => $type,
                'value' => $value,
                'value2' => $value
            ]);
        }
    }

    /** @noinspection PhpUnused */

    public function setInt(Selector $selector, int $value)
    {
        $this->setValue($selector, 'int', $value);
    }
}
