<?php

namespace Smerteliko\MicroCli\Config;

use Smerteliko\MicroCli\Config\Loader\LoaderInterface;
use RuntimeException;

class Config
{
    /** @var LoaderInterface[] */
    private array $loaders = [];
    private array $data = [];

    public function addLoader(LoaderInterface $loader): self
    {
        $this->loaders[] = $loader;
        return $this;
    }

    public function load(string $filePath): void
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($filePath)) {
                $loadedData = $loader->load($filePath);
                // Recursively merge configurations so we can load multiple files
                $this->data = array_replace_recursive($this->data, $loadedData);
                return;
            }
        }

        throw new RuntimeException(sprintf('No configuration loader found for file "%s".', $filePath));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $array = $this->data;

        foreach ($keys as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    public function all(): array
    {
        return $this->data;
    }
}