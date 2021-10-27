<?php

namespace Phore\UniDb\Helper;

class ImportReport
{
    public ?int $totalRecords = null;
    public int $recordsProcessed = 0;
    public int $recordsUpdated = 0;
    public int $recordsCreated = 0;
    public int $recordsDeleted = 0;

    public array $warnings = [];
}