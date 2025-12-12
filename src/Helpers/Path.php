<?php

namespace Felora\Helpers;

use Closure;
use Felora\Contracts\Helpers\Path as PathContract;

/**
 * Class Path
 *
 * Helper class for handling file and directory paths.
 * Can validate if paths exist as files or directories.
 *
 * @package Felora\Helpers
 */
class Path implements PathContract
{
    /**
     * @var string[] List of paths being validated
     */
    private array $paths = [];

    private Closure $exception;

    /**
     * Path constructor.
     *
     * @param string|string[] $path Single path or array of paths
     */
    public function __construct(string|array $path = [])
    {
        $this->add($path);
    }

    /**
     * Add paths to the current Path instance.
     *
     * @param string|string[] $path Single path or array of paths
     * @return PathContract
     */
    public function on(string|array $path): PathContract
    {
        $this->add($path);
        return $this;
    }

    /**
     * Check if all paths are valid directories.
     *
     * @return bool True if all paths exist and are directories, false otherwise
     */
    public function isDir(): bool
    {
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if all paths are valid files.
     *
     * @return bool True if all paths exist and are files, false otherwise
     */
    public function isFile(): bool
    {
        foreach ($this->paths as $path) {
            if (!is_file($path)) {
                return false;
            }
        }

        return true;
    }

    public function exception(Closure $exception): PathContract
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * Add paths to the internal array.
     *
     * @param string|string[] $path Single path or array of paths
     * @return void
     */
    protected function add(string|array $path): void
    {
        if (is_string($path)) {
            $this->paths[] = $path;
        } else {
            // Replace all current paths with the new array
            $this->paths = $path;
        }
    }

    /**
     * Get all paths currently stored in the instance.
     *
     * @return string[]
     */
    public function get(): array
    {
        return $this->paths;
    }
}
