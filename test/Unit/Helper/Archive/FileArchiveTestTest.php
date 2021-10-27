<?php

namespace Test\Unit\Helper\Archive;

use Phore\UniDb\Helper\Archive\FilesystemArchive;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class FilesystemArchiveTest extends TestCase
{




    public function testWriteFile()
    {
        $testPath = "/tmp/" . uniqid();
        $archive = new FilesystemArchive($testPath);

        $archive->setContents("/a.txt", "data");

        $this->assertEquals("data", file_get_contents($testPath . "/a.txt"));
        $this->assertEquals("data", $archive->getContents("/a.txt"));
    }

    public function testListFile()
    {
        $testPath = "/tmp/" . uniqid();
        $archive = new FilesystemArchive($testPath);

        $this->assertEquals([], $archive->list());
        $archive->setContents("/a.txt", "a");

        $this->assertEquals(["/a.txt"], $archive->list());

    }


}