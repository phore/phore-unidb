<?php


namespace Phore\UniDb\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UniDbColumn
{
    public function __construct(
        public bool $primaryKey = false,
        public string $type = "VARCHAR(255)",
        public ?string $column = null,
        public bool $notNull = false
    ){}
}