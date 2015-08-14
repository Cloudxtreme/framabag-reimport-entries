<?php

namespace Wallabag\Reimport\Service;

class Database
{
    private $pdo;

    public function __construct($path)
    {
        $this->pdo = new \PDO('sqlite:'.$path.'/poche.sqlite');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function getPdo()
    {
        return $this->pdo;
    }
}
