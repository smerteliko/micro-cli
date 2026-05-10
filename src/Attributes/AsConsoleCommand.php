<?php

namespace Smerteliko\MicroCli\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsConsoleCommand
{
	public function __construct(
		public string $name,
		public string $description = ''
	) {
	}
}