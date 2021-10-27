<?php

namespace Phore\UniDb\Helper\IOStrategy;

use Phore\UniDb\Helper\Archive\Archive;
use Phore\UniDb\Helper\Csv;
use Phore\UniDb\Helper\ImportReport;
use Phore\UniDb\Schema\TableSchema;
use Phore\UniDb\UniDb;

class CsvFileIoStrategy implements IOStrategy
{

    public function __construct (
        public string    $prefix = "",
        public ?string    $fileName = null,
        public string    $separator = ";",
        public bool      $validate = true,

        /**
         *
         * <example>
         * fn(string|array $data, string $fileNameId, string $fileName) => ["id"=>$fileNameId, "textdata1" => $data]
         * </example>
         *
         * @var \Closure|null
         */
        public ?\Closure $importFilter = null,
        public ?\Closure $exportFilter = null
    ){

    }

    public function import(UniDb $uniDb, string $tableName, Archive $srcArchive, callable $progress = null): ImportReport
    {
        $report = new ImportReport();
        $tableSchema = $uniDb->schema->getSchema($tableName);

        $filename = $this->fileName;
        if ($filename === null) {
            $filename = $tableSchema->getTableName() . ".csv";
        }

        $csv = new Csv($res = $srcArchive->getFileResource($this->prefix . "/" . $filename), $this->separator);
        foreach ($csv->read() as $data) {
            if ($this->importFilter !== null)
                $data = ($this->importFilter)(data: $data);

            if ($data === null)
                continue;

            if ($this->validate) {
                $data = $tableSchema->hydrateEntity($data);
            }

            $uniDb->insert($data, $tableName, true);
            $report->recordsProcessed++;
            $report->recordsUpdated++;
            if ($progress !== null)
                $progress($report);
        }
        fclose ($res);

        return $report;
    }

    public function export(UniDb $uniDb, string $tableName, Archive $targetArchive, callable $progress = null) : ImportReport
    {
        $report = new ImportReport();
        $tableSchema = $uniDb->schema->getSchema($tableName);

        $filename = $this->fileName;
        if ($filename === null) {
            $filename = $tableSchema->getTableName() . ".csv";
        }
        $csvFp = fopen($tempName = tempnam("/tmp", "csvexp"), "wb+");

        $result = $uniDb->query(table: $tableName,  count: $count);
        $report->totalRecords = $count;

        $headerSend = false;
        foreach ($result as $data) {
            if ($this->exportFilter !== null)
                $data = ($this->exportFilter)($data);

            if ($data === null)
                continue;

            if ( ! $headerSend) {
                fputcsv($csvFp, array_keys($data), $this->separator);
                $headerSend = true;
            }
            fputcsv($csvFp, array_values($data), $this->separator);

            $report->recordsProcessed++;
            if ($progress !== null)
                $progress($report);
        }
        fclose($csvFp);
        $targetArchive->addFile($filename, $tempName, true);
        return $report;
    }

}