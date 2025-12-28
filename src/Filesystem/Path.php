<?php

namespace Felora\Filesystem;

use Closure;
use Felora\Contracts\Filesystem\Path as PathContract;
use Felora\Filesystem\Traits\PathFiltering;
use Felora\Filesystem\Traits\PathValidation;

/**
 * Class Path
 *
 * A utility class for managing and interacting with file system paths.
 *
 * @package Felora\Filesystem
 */
class Path implements PathContract
{
    use PathValidation, PathFiltering;

    /**
     * @var string[] List of paths currently managed by the instance
     */
    private array $paths = [];

    /**
     * Optional exception handler callback.
     *
     * When defined, this callback is invoked with the failing path
     * instead of throwing a RuntimeException during validation or assertions.
     *
     * @var Closure|null
     */
    private ?Closure $exception = null;

    /**
     * Path constructor.
     *
     * @param string|string[] $path Single path or array of paths to initialize
     */
    public function __construct(string|array $path = [])
    {
        $this->add($path);
    }

    /**
     * Add one or multiple paths to the instance.
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
     * Register a custom exception handler callback.
     *
     * This callback will be executed with the failing path when
     * an assertion fails, instead of throwing a RuntimeException.
     *
     * @param Closure(string): void $exception
     * @return PathContract
     */
    public function exception(Closure $exception): PathContract
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * Recursively traverse stored directories and add their subpaths.
     *
     * This method uses PathScanner internally to explore directories
     * up to a specified depth. Can optionally return only items at the exact depth level.
     *
     * @param int $depth Maximum depth to traverse (0 = unlimited)
     * @param bool $onlyCurrentLevel If true, only include items exactly at the specified depth
     * @return PathContract
     */
    public function subPath(int $depth = 0, bool $onlyCurrentLevel = false): PathContract
    {
        $paths = $this->paths;
        $this->clean();

        foreach ($paths as $path) {
            $scanner = new PathScanner($path, $depth, $onlyCurrentLevel);

            foreach ($scanner->scan() as $file) {
                $this->add($file->getRealPath());
            }
        }

        return $this;
    }

    /**
     * Determine whether the given path is a valid file.
     *
     * @param string $path
     * @return bool True if the path exists and is a file
     */
    protected function hasFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * Determine whether the given path is a valid directory.
     *
     * @param string $path
     * @return bool True if the path exists and is a directory
     */
    protected function hasDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Add paths to the internal list.
     *
     * If an array is provided, it replaces the current list.
     *
     * @param string|string[] $path Single path or array of paths
     * @return void
     */
    protected function add(string|array $path): void
    {
        if (is_string($path)) {
            $this->paths[] = $path;
        } else {
            $this->paths = $path;
        }
    }

    /**
     * Retrieve all stored paths.
     *
     * @return string[] Array of stored paths
     */
    public function get(): array
    {
        return $this->paths;
    }

    /**
     * Clear all stored paths.
     *
     * @return void
     */
    protected function clean(): void
    {
        $this->paths = [];
    }
}
