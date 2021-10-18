<?php


namespace Phore\UniDb\Test;



use Phore\UniDb\Schema\Schema;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{

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