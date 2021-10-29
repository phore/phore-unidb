<?php

namespace Phore\UniDb\Helper\Archive;

class FilesystemArchive implements Archive
{

    public function __construct (
        public string $path
    ){}


    private function scandir($dir) : array
    {
        $result = [];
        foreach(scandir($dir) as $filename) {
            if ($filename === '.' || $filename === "..")
                continue;
            $filePath = $dir . '/' . $filename;
            if (is_dir($filePath)) {
                foreach ($this->scandir($filePath) as $childFilename) {
                    $result[] = $childFilename;
                }
            } else {
                $result[] = str_replace("//", "/", $dir . "/" . $filename);
            }
        }
        return $result;
    }

    public function list(string $prefix = null): \Generator|array
    {
        $files = $this->scandir($this->path);
        print_r ($files);
        if ($files === false)
            throw new \InvalidArgumentException("Cannot read index vom directory: '$this->path'");
        $ret = [];
        if (startsWith( $prefix, "/"))
            $prefix = substr($prefix, 1);
        foreach ($files as $file) {

            $name = str_replace("//", "/", substr($file, strlen($this->path)));
            if (startsWith($name, "/"))
                $name = substr($name, 1);
            echo $name;
            if ($prefix !== null && ! str_starts_with($name, $prefix))
                continue;
            $ret[] = $name;
        }
        return $ret;
    }

    public function getFileResource(string $path)
    {
        $file = $this->path . "/". $path;
        $fd = fopen($file, "r");

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
