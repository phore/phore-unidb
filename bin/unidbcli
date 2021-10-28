#!/bin/php
<?php


namespace OticTools;

use Phore\Tester\PTestCli;
use Phore\UniDb\Cli\UniDbCli;

if (file_exists(__DIR__ . "/../vendor/autoload.php")) {
    require __DIR__ . "/../vendor/autoload.php";
} else {
    require __DIR__ . "/../../../autoload.php";
}

(new UniDbCli())->main($argv, $argc);
