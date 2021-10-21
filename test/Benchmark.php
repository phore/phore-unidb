<?php


namespace Test;


use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\UniDb;
use PHPUnit\Framework\TestCase;
use Test\Entities\TestEntity1;
use Test\Entities\TestEntity2;

class Benchmark extends TestCase
{


    public function testBenchmark()
    {
        $udb = new UniDb(new SqliteDriver(new \PDO("sqlite::memory:")), new Schema([
            TestEntity1::class, TestEntity2::class
        ]));

        $udb->createSchema();

        for ($i=0; $i<1000000; $i++) {
            $udb->insert(new TestEntity1("test$i"));
        }

        //for ($i=0; $i<1000000; $i++) {
        //    $udb->insert(new TestEntity2("test$i", "dataas"));
        //}
    }

}