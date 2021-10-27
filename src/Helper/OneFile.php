<?php


namespace Phore\UniDb\Helper;


use Phore\UniDb\Schema\TableSchema;

class OneFile implements Writer
{

    public function __construct(
        private string $outdir,
        private string $format = "yaml"
    ){}




}