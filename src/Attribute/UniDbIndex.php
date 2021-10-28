<?php

namespace Phore\UniDb\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class UniDbIndex
{
    public function __construct(
        public array $cols = [],
        public string $type = "UNIQUE",
        public ?string $name = null
    ){}
}
