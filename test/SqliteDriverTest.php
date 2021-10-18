<?php


namespace Phore\UniDb\Test;


use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Stmt\Stmt;
use Phore\UniDb\UniDb;
use PHPUnit\Framework\TestCase;

class SqliteDriverTest extends TestCase
{

    public function testWriteRead()
    {

        $udb = new UniDb(new SqliteDriver(new \PDO("sqlite::memory:")), new Schema([
            "Tbl1" => []
        ]));

        $udb->createSchema();;

        $udb->insert("Tbl1", ["tbl1_id"=>"wurst", "data" => "abc"]);
        $udb->update("Tbl1", ["tbl1_id"=>"wurst", "data" => "abcd"]);

        foreach ($udb->query("Tbl1", new Stmt(["tbl1_id", "=", "wurst"])) as $data) {
            print_R ($data);
        }

    }

}