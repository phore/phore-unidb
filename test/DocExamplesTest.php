<?php


namespace Phore\UniDb\Test;


use http\Client\Curl\User;
use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Stmt\OrStmt;
use Phore\UniDb\Stmt\Stmt;
use Phore\UniDb\UniDb;
use PHPUnit\Framework\TestCase;



/**
 * Class DocExamplesTest
 * @package Phore\UniDb\Test
 * @internal
 */
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


    public function testCastingExample()
    {
        // Setup table structure and driver
        $udb = new UniDb(
            new SqliteDriver(new \PDO("sqlite::memory:")),
            new Schema(
                [
                    "User" => [
                        "class" => UserEntity::class,
                        "indexes" => ["user_name"]
                    ]
                ]
            )
        );

        $udb->createSchema();

        $entity = new UserEntity();
        $entity->user_id = "user1";
        $entity->user_name = "Bob";

        $udb->insert($entity); // No need to specify the table

        foreach ($udb->with(UserEntity::class)->query(stmt: ["user_name", "=", "Bob"], cast: true) as $user) {
            $user instanceof UserEntity ?? throw new \InvalidArgumentException();
            echo $user->user_name;
        }


    }


}

/**
 * Class UserEntity
 * @package Phore\UniDb\Test
 * @internal
 */
class UserEntity {
    /**
     * @var string
     */
    public $user_id;

    /**
     * @var string
     */
    public $user_name;
}