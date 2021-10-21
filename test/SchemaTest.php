<?php


namespace Phore\UniDb\Test;



use Phore\UniDb\Schema\EntitySchema;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Schema\TableSchema;
use PHPUnit\Framework\TestCase;
use Test\Entities\TestEntity1;

class SchemaTest extends TestCase
{

    public function testEntitySchema()
    {
        $schema = new EntitySchema(TestEntity1::class);

        $this->assertEquals(["entity_id"], $schema->getPkCols());
    }



    public function testSchema()
    {
        $schema = new Schema([
            "Table1" => [
                "pk_col" => "col1",
                "index_cols" => ["col2"],
                "data_col" => "_data"
            ]
        ]);

    }





}