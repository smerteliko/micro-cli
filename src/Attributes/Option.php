<?php

namespace Smerteliko\MicroCli\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Option
{
    public function __construct(
        public ?string $shortcut = null,
        public string $description = '',
        public bool $required = false,
        public mixed $default = false
    ) {
    }
}