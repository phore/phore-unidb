<?php

namespace Phore\UniDb\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class UniDbIndex
{
    public function __construct(
        public array $cols = [],
        /**
         * UNIQUE or INDEX
         * @var string
         */
        public string $type = "INDEX",
        public ?string $name = null
    ){}
}
