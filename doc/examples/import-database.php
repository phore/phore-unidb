<?php
namespace Docs;

use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Helper\IOStrategy\CsvFileIoStrategy;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\UniDb;
use Phore\UniDb\UniDbConfig;
use Test\Entities\TestEntity2;
use Test\Entities\TestEntity3;

$udb = UniDbConfig::factory("sqlite::memory:", [
    TestEntity2::class, TestEntity3::class
], true);


$udb->import(path: "/tmp/", strategies: [
    TestEntity3::class => new CsvFileIoStrategy()
]);

foreach ($udb->query(table: TestEntity3::class) as $cur) {
     print_r($cur);
};


