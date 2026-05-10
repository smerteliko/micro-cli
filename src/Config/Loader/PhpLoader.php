<?php

namespace Smerteliko\MicroCli\Config\Loader;

use RuntimeException;

class PhpLoader implements LoaderInterface
{
	public function load(string $filePath): array
	{
		if (!file_exists($filePath)) {
			throw new RuntimeException(sprintf('File "%s" not found.', $filePath));
		}

		$config = require $filePath;

		if (!is_array($config)) {
			throw new RuntimeException(sprintf('PHP configuration file "%s" must return an array.', $filePath));
		}

		return $config;
	}

	public function supports(string $filePath): bool
	{
		return str_ends_with($filePath, '.php');
	}
}