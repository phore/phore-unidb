<?php

namespace Phore\UniDb\Helper\Archive;

class FilesystemArchive implements Archive
{

    public function __construct (
        public string $path
    ){}

    public function list(string $prefix = null): \Generator|array
    {
        $files = glob($this->path . "/**");
        if ($files === false)
            throw new \InvalidArgumentException("Cannot read index vom directory: '$this->path'");
        $ret = [];
        foreach ($files as $file) {
            if ($prefix !== null && ! str_starts_with($file, $prefix))
                continue;
            $ret[] = substr($file, strlen($this->path));
        }
        return $ret;
    }

    public function getFileResource(string $path, string $mode = "rb")
    {
        $fd = fopen($this->path . "/". $path, $mode);
        if ($fd === false)
            throw new \InvalidArgumentException("Cannot open file '$this->path/$path' in mode '$mode'");
        return $fd;
    }


    public function getContents(string $path): string
    {
        $file = $this->path . "/" . $path;
        $data = file_get_contents($file);
        if ($data === false)
            throw new \RuntimeException("Cannot open file '$file'");
        return $data;
    }

    public function setContents(string $path, string $contents)
    {
        $targetFile = $this->path . "/$path";
        $targetDir = dirname($targetFile);
        if ( ! is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDir));
            }
        }
        if ( ! file_put_contents($targetFile, $contents))
            throw new \RuntimeException("Cannot setContents on file '$targetFile'");
    }

    private $autodeleteList = [];

    public function addFile(string $path, string $localFile, bool $autodelete = false)
    {
        $targetFile = $this->path . "/$path";
        $targetDir = dirname($targetFile);
        if ( ! is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDir));
            }
        }
        if ( ! copy($localFile, $targetFile))
            throw new \RuntimeException("Copy operation failed: '$localFile' => '$targetFile'");
        if ($autodelete)
            $this->autodeleteList[] = $localFile;
    }

    public function __destruct()
    {
        foreach ($this->autodeleteList as $file)
            unlink($file);
    }
}