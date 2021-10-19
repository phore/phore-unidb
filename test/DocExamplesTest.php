<?php


namespace Phore\UniDb\Test;


use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Stmt\OrStmt;
use Phore\UniDb\Stmt\Stmt;
use Phore\UniDb\UniDb;
use PHPUnit\Framework\TestCase;

class DocExamplesTest extends TestCase
{

    public function testBasicExample()
    {
        // Setup table structure and driver
        $udb = new UniDb(
            new SqliteDriver(new \PDO("sqlite::memory:")),
            new Schema(
                [
                    "User" => [
                        "indexes" => ["user_name"]
                    ]
                ]
            )
        );

        // Create the schema (if it does not already exist)
        echo $udb->createSchema();

        // Select the 'User' Table
        $userTbl = $udb->with("User");

        // Insert two entities
        $userTbl->insert(["user_id"=>"user1", "user_name" => "Bob"]);
        $userTbl->insert(["user_id"=>"user2", "user_name" => "Alice"]);

        // Query all datasets with user_name='Bob' OR user_name='Alice'
        foreach ($userTbl->query(stmt: new OrStmt(["user_id", "=", "Bob"], ["user_id", "=", "Alice"])) as $data) {
            print_R ($data);
        }

    }
}