<?php

namespace Phore\UniDb\Helper\IOStrategy;

use Phore\UniDb\Helper\Archive\Archive;
use Phore\UniDb\Helper\ImportReport;
use Phore\UniDb\Schema\TableSchema;
use Phore\UniDb\UniDb;

class OneFilePerEntityIoStrategy implements IOStrategy
{

    public function __construct (
        public string    $prefix = "",
        public string    $fileExtension = "yml",
        public ?string   $filenameColumn = null,
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
    ){}

    public function import(UniDb $uniDb, string $tableName, Archive $srcArchive, callable $progress = null): ImportReport
    {
        $result = new ImportReport();
        $tableSchema = $uniDb->schema->getSchema($tableName);

        foreach ($srcArchive->list($tableSchema->getTableName()) as $file) {
            try {
                if (pathinfo($file, PATHINFO_EXTENSION) !== $this->fileExtension) {
                    $result->warnings[] = "Ignoring: '$file' - extension does not match '$this->fileExtension'";
                    continue;
                }
                $fileNameId = pathinfo($file, PATHINFO_FILENAME);
                switch(pathinfo($file, PATHINFO_EXTENSION)) {
                    case "yaml":
                    case "yml":
                        $data = yaml_parse($srcArchive->getContents($file));
                        break;
                    case "json":
                        $data = json_decode($srcArchive->getContents($file));
                        break;
                    default:
                        $data = $srcArchive->getContents($file);

                }

                if ($this->importFilter !== null)
                    $data = ($this->importFilter)(data: $data, fileNameId: $fileNameId, file: $file);

                if ($data === null)
                    continue;

                if ($this->validate) {
                    $data = $tableSchema->hydrateEntity($data);
                }

                $uniDb->insert($data, $tableName, true);
                $result->recordsProcessed++;
                $result->recordsUpdated++;
                if ($progress !== null)
                    $progress($result);
            } catch (\Exception $e) {
                throw new \Exception("Import error on file '$file': {$e->getMessage()}", 0, $e);
            }
        }
        return $result;
    }

    public function export(UniDb $uniDb, string $tableName, Archive $targetArchive, callable $progress = null) : ImportReport
    {
        $report = new ImportReport();
        $tableSchema = $uniDb->schema->getSchema($tableName);

        $filenameColumn = $this->filenameColumn;
        if ($filenameColumn === null) {
            if (count ($tableSchema->getPkCols()) !== 1)
                throw new \InvalidArgumentException("Cannot autodetect filename column on '$tableName': Please specify filenameColumn");
            $filenameColumn = $tableSchema->getPkCols()[0];
        }

        $result = $uniDb->query(table: $tableName,  count: $count);
        $report->totalRecords = $count;

        foreach ($result as $data) {
            $filename = $tableSchema->getTableName() . "/" . $data[$filenameColumn] . ".". $this->fileExtension;

            if ($this->exportFilter !== null)
                $data = ($this->exportFilter)($data);

            switch($this->fileExtension) {
                case "yml":
                case "yaml":
                    $data = yaml_emit($data);
                    break;
                case "json":
                    $data = json_encode($data);
                    break;
            }

            $targetArchive->setContents($filename, $data);
            $report->recordsProcessed++;
            if ($progress !== null)
                $progress($report);
        }
        return $report;
    }

}
