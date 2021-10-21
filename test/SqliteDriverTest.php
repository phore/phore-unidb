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
            "Tbl1" => [
                "columns" => [
                    "tbl1_id" => "string",
                    "data" => "text"
                ]
            ]
        ]));

        $udb->createSchema();;

        $udb->insert(["tbl1_id"=>"wurst", "data" => "abc"], "Tbl1");
        $udb->update(["tbl1_id"=>"wurst", "data" => "abcd"], "Tbl1");

        foreach ($udb->query(table: "Tbl1", stmt: new Stmt(["tbl1_id", "=", "wurst"])) as $data) {
            print_R ($data);
        }
    }


    public function testExtendedDoc()
    {
        $udb = new UniDb(new SqliteDriver(new \PDO("sqlite::memory:")), new Schema([
            "Tbl1" => [
                "columns" => [
                    "tbl1_id" => "string",
                    "data" => "text"
                ]
            ]
        ]));

        $udb->createSchema();;

        $udb->insert(["tbl1_id"=>"wurst", "data" => "abc"], "Tbl1");
        $udb->insert(["tbl1_id"=>"wurst123", "data" => "abcd"], "Tbl1");

        $udb->query(table: "Tbl1", stmt: new Stmt(["tbl1_id", "<>", ""]), limit: 1, page: 1);

        print_r($udb->result->getResult());

    }


}