<?php

namespace Smerteliko\MicroCli\Config\Loader;

use RuntimeException;

class XmlLoader implements LoaderInterface
{
	public function load(string $filePath): array
	{
		if (!file_exists($filePath)) {
			throw new RuntimeException(sprintf('File "%s" not found.', $filePath));
		}

		libxml_use_internal_errors(true);
		$xml = simplexml_load_file($filePath);

		if ($xml === false) {
			$errors = libxml_get_errors();
			libxml_clear_errors();
			$errorMessage = $errors[0]->message ?? 'Unknown XML error';
			throw new RuntimeException(sprintf('Invalid XML in "%s": %s', $filePath, $errorMessage));
		}

		// Convert SimpleXMLElement to an associative array
		$json = json_encode($xml);
		$config = json_decode($json, true);

		return is_array($config) ? $config : [];
	}

	public function supports(string $filePath): bool
	{
		return str_ends_with($filePath, '.xml');
	}
}