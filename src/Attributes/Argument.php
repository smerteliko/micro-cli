<?php

namespace Smerteliko\MicroCli\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Argument
{
	public function __construct(
		public string $description = '',
		public bool $required = false,
		public mixed $default = null
	) {
	}
}