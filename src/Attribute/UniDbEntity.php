<?php


namespace Phore\UniDb\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class UniDbEntity
{

    public function __construct(
        public array|string $pk = [],
        public string $pk_create_strategy = "UUID",
        public ?string $table = null,
        public string|null $json_data_col = null
    ){}


}