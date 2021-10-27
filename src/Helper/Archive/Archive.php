<?php

namespace Phore\UniDb\Helper\Archive;

interface Archive
{
    public function list(string $prefix = null) : \Generator|array;

    public function getFileResource(string $path);

    public function getContents(string $path) : string;

    public function setContents(string $path, string $contents);

    public function addFile(string $path, string $localFile, bool $autodelete=false);

}