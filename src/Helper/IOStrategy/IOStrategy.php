<?php

namespace Phore\UniDb\Helper\IOStrategy;

use Phore\UniDb\Helper\Archive\Archive;
use Phore\UniDb\Helper\ImportReport;
use Phore\UniDb\UniDb;

interface IOStrategy
{

    const REPLACE_EXISTING = "REPLACE_EXISTING";


    public function import(UniDb $uniDb, string $tableName, Archive $srcArchive, callable $progress = null) : ImportReport;
    public function export(UniDb $uniDb, string $tableName, Archive $targetArchive, callable $progress = null) : ImportReport;

}