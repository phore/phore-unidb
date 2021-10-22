<?php

namespace Test;


use Phore\UniDb\UniDbConfig;
use Test\Entities\TestEntity1;
use Test\Entities\TestEntity2;

UniDbConfig::define("sqlite:/tmp/db.sq3", [TestEntity1::class, TestEntity2::class], autocreate_schema: true);


UniDbConfig::get()->insert($e = new TestEntity2(null, "sldk"));
echo $e->entity_id;