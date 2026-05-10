<?php

namespace Smerteliko\MicroCli\Config\Loader;

interface LoaderInterface
{
    /**
     * Parses the file and returns its content as an array.
     */
    public function load(string $filePath): array;

    /**
     * Checks if this loader supports the given file (usually by extension).
     */
    public function supports(string $filePath): bool;
}