<?php

namespace Phector\Tests;

use Phector\Repo;

final class DB
{
    public static $postgresConfig = [
        'driver' => 'pgsql',
        'username' => 'postgres',
        'password' => '',
        'database' => 'phector_test',
        'host' => 'localhost',
        'port' => 5432,
        'charset' => 'utf8'
    ];

    public static function repo() : Repo
    {
        return Repo::create(self::$postgresConfig);
    }

    public static function schemaBuilder()
    {
        return self::repo()->getManager()->schema();
    }
}
