<?php
namespace Docs;

use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\UniDb;

$udb = new UniDb(
    new SqliteDriver(new \PDO("sqlite::memory:")),
    new Schema(
        [
            "User" => [
                "columns" => [
                    "user_id" => "int",
                    "user_name" => "string"
                ],
                "indexes" => []
            ]
        ]
    )
);

$udb->createSchema();

