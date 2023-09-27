<?php

namespace App\Database\Connection;

use PDO;

class Connection
{
    private static ?PDO $connection;

    public static function setConnection(?PDO $connection = null)
    {
        if (!$connection) {
            self::$connection = new PDO(
                dsn: 'mysql:host=local.db;dbname=my_database',
                username: 'my_user',
                password: 'my_password',
                options: [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ]);
        }

        return self::$connection;
    }
}
