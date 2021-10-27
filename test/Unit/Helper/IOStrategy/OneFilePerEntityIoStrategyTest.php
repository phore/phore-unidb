<?php

namespace Test\Unit\Helper;

use Phore\UniDb\Helper\Archive\FilesystemArchive;
use Phore\UniDb\Helper\IOStrategy\OneFilePerEntityIoStrategy;
use Phore\UniDb\UniDbConfig;
use PHPUnit\Framework\TestCase;
use Test\Entities\TestEntity1;
use Test\Entities\TestEntity2;
use Test\Entities\TestEntity3;

/**
 * @internal
 */
class OneFilePerEntityIoStrategyTest extends TestCase
{


    public function testExportBase()
    {
        $testpath = "/tmp/" . uniqid();
        $archive = new FilesystemArchive($testpath);
        $unidb = UniDbConfig::factory("sqlite::memory:", [TestEntity1::class, TestEntity2::class], true);

        $unidb->insert(new TestEntity1("a"));



        $ioStrategy = new OneFilePerEntityIoStrategy();

        $ioStrategy->export($unidb, TestEntity1::class, $archive);
        $ioStrategy->import($unidb, TestEntity1::class, $archive);
    }

    public function testInputOutputFilter()
    {
        $testpath = "/tmp/" . uniqid();
        $archive = new FilesystemArchive($testpath);
        $unidb = UniDbConfig::factory("sqlite::memory:", [TestEntity3::class], true);

        $unidb->insert(new TestEntity3("Key1", "SomeText"));

        $ioStrategy = new OneFilePerEntityIoStrategy(
            fileExtension: "txt",
            importFilter: fn($data, $fileNameId) => ["entity_id" => $fileNameId, "textdata1" => $data],
            exportFilter: fn($data) => $data["textdata1"],
        );


        $ioStrategy->export($unidb, TestEntity3::class, $archive);
        $this->assertEquals("SomeText", $archive->getContents("TestEntity3/Key1.txt"));

        $ioStrategy->import($unidb, TestEntity3::class, $archive);

        $entity = $unidb->select(byPrimaryKey: "Key1", table: TestEntity3::class, cast: true);
        $entity instanceof TestEntity3 ?? throw new \Exception("Invalid type");

        $this->assertEquals("SomeText", $entity->textdata1);
    }


}