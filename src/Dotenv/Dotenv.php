<?php

namespace Smerteliko\MicroCli\Dotenv;

use RuntimeException;

class Dotenv
{
    /**
     * Loads environment variables from a .env file.
     */
    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException(sprintf('Environment file not found: "%s"', $path));
        }

        // Read file into an array, ignoring empty lines
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments
            if (str_starts_with($line, '#')) {
                continue;
            }

            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$name, $value] = explode('=', $line, 2);

                $name = trim($name);
                // Remove spaces and quotes (both single and double) from the value
                $value = trim($value, " \t\n\r\0\x0B\"'");

                // Only set if not already defined in the environment (system env vars have priority)
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}