<?php

namespace Test;


use Phore\UniDb\Helper\IOStrategy\CsvFileIoStrategy;
use Phore\UniDb\UniDbConfig;
use Test\Entities\TestEntity1;
use Test\Entities\TestEntity2;
use Test\Entities\TestEntity3;

UniDbConfig::define("sqlite:/tmp/db.sq3", [TestEntity1::class, TestEntity3::class], autocreate_schema: true);
UniDbConfig::defineIO([
    TestEntity3::class => new CsvFileIoStrategy()
]);

UniDbConfig::get()->insert($e = new TestEntity3(null, "sldk"));
echo $e->entity_id;