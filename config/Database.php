<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function getInstance(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                "mysql:host=localhost;dbname=swaply;charset=utf8",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }

        return self::$pdo;
    }

    private function __construct() {}
}