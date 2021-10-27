<?php

namespace Phore\UniDb\Helper;

use Phore\UniDb\Helper\IOStrategy\IOStrategy;
use Phore\UniDb\UniDb;

class Importer
{
    public function __construct (
        public UniDb $uniDb,
        public ?string $path = null,
        public ?string $zipFile = null,
        public ?array $strategy = null,
    ){}





    public function import ()
    {
        foreach ($this->strategy as $tableName => $strategy) {
            $strategy instanceof IOStrategy ?? throw new \InvalidArgumentException("Invalid IOStrategy for table '$tableName'");

        }
    }


}