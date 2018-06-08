<?php

namespace Phector\Tests\CustomTypeTest;

use Phector\Repo;

use Phector\Tests\CustomTypeTest\FooType;

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

    public static $customTypes = [
        'foo' => FooType::class
    ];

    public static function repo() : Repo
    {
        return Repo::create([
            'db' => self::$postgresConfig,
            'types' => self::$customTypes
        ]);
    }
}
