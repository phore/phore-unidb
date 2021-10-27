<?php

namespace Phore\UniDb\Helper;

class Csv
{

    public function __construct(
        public $fileOrDescriptor,
        public string $separator = ";"
    ){}


    /**
     * @param int|null $length
     * @param bool $strict
     * @return \Generator|array
     */
    public function read(int $length=null, bool $strict = true) : \Generator|array
    {
        $fileHandle = $this->fileOrDescriptor;
        if ( ! is_resource($fileHandle)) {
            $fileHandle = fopen($this->fileOrDescriptor, "rb");
            if ($fileHandle === false)
                throw new \InvalidArgumentException("Cannot open '$this->fileOrDescriptor' for reading.");
        }

        $data = fgetcsv($fileHandle, $length, $this->separator);
        if ($data === false)
            throw new \InvalidArgumentException("Error reading first line from '$this->fileOrDescriptor'");

        $mapArray = $data;

        if (count($mapArray) < 1)
            throw new \InvalidArgumentException("Csv file must contain at least 1 Column (one separator '$this->separator')");

        $line = 1;
        while ( ! feof($fileHandle)) {
            $data = fgetcsv($fileHandle, $length, $this->separator);
            $line++;
            if ($data === false)
                break;
            if ($data === [null])
                continue;
            $ret = [];

            foreach ($mapArray as $index => $name) {
                if (trim($name) === "")
                    continue;
                if ( ! isset ($data[$index])) {
                    if ($strict)
                        throw new \InvalidArgumentException("Out of bound index $index missing in file '$this->fileOrDescriptor' on line $line");
                    $data[$index] = null;
                }

                $ret[$name] = $data[$index];
            }
            yield $ret;
        }
    }

}