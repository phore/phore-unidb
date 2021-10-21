<?php


namespace Test\Unit;


use Phore\Tester\Attr\ApplyFixture;
use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Schema\Schema;
use PHPUnit\Framework\TestCase;

class SqliteDriverSchemaTest extends TestCase
{


    #[ApplyFixture(path: __DIR__ . "/cases")]
    public function testSchemaCases($test)
    {

        $schemaIn = require($test. ".in.php");
        $compareFile = file_get_contents($test. ".out.txt");

        $schema = SqliteDriver::buildCreateTableStmt((new Schema($schemaIn))->getSchema("Tbl1"));

        $this->assertEquals($compareFile, $schema);
    }

}