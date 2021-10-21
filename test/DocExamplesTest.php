<?php


namespace Phore\UniDb\Test;


use http\Client\Curl\User;
use Phore\Tester\Attr\ApplyFixture;
use Phore\UniDb\Attribute\UniDbColumn;
use Phore\UniDb\Driver\Sqlite\SqliteDriver;
use Phore\UniDb\Schema\Schema;
use Phore\UniDb\Stmt\OrStmt;
use Phore\UniDb\Stmt\Stmt;
use Phore\UniDb\UniDb;
use PHPUnit\Framework\TestCase;



/**
 * Class DocExamplesTest
 * @package Phore\UniDb\Test
 * @internal
 */
class DocExamplesTest extends TestCase
{

    #[ApplyFixture(__DIR__ . "/../doc/examples")]
    public function testExample($file)
    {
        require __DIR__ . "/../" . $file . ".php";
    }
}

