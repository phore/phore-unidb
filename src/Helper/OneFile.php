<?php


namespace Phore\UniDb\Helper;


use Phore\UniDb\Schema\TableSchema;

class OneFile implements Writer
{

    public function __construct(
        private string $outdir,
        private string $format = "yaml"
    ){}


    public function write(TableSchema $schema, $data)
    {
        $pkCols = $schema->getPkCols();
        if (count ($pkCols) == 1) {
            $targetDir = $this->outdir . "/{$schema->getTableName()}";
            if ( ! is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDir));
                }
            }
            yaml_emit_file($targetDir . "/{$data[$pkCols[0]]}.yaml", $data);
        } else {
            file_put_contents($this->outdir . "/{$schema->getTableName()}.yaml", yaml_emit($data), FILE_APPEND);
        }

    }

}