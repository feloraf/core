<?php

namespace Felora\Contracts\Filesystem;

use Closure;

/**
 * Interface Path
 *
 * Contract for handling file and directory paths.
 * Provides methods to manage a list of file system paths,
 * check their existence and type, filter them, and traverse directories.
 *
 * @package Felora\Contracts\Filesystem
 */
interface Path
{
    /**
     * Check if all stored paths are valid directories.
     *
     * @return bool True if all paths exist and are directories, false otherwise
     */
    public function isDir(): bool;

    /**
     * Check if all stored paths are valid files.
     *
     * @return bool True if all paths exist and are files, false otherwise
     */
    public function isFile(): bool;

    /**
     * Retrieve all paths currently stored in the instance.
     *
     * @return string[] Array of stored paths
     */
    public function get(): array;

    /**
     * Register a custom exception handler callback.
     *
     * The callback will be executed with the failing path when an assertion fails
     * instead of throwing a RuntimeException.
     *
     * @param Closure(string): void $exception Callback that receives the failing path
     * @return static Returns $this for method chaining
     */
    public function exception(Closure $exception): Path;

    /**
     * Recursively traverse stored directories and add subpaths.
     *
     * Uses an internal scanner to traverse directories up to the specified depth.
     *
     * @param int $depth Maximum depth to traverse (0 = unlimited)
     * @param bool $onlyCurrentLevel If true, only include items at the exact depth level
     * @return static Returns $this for method chaining
     */
    public function subPath(int $depth = 0, bool $onlyCurrentLevel = false): Path;

    /**
     * Filter the stored paths to include only directories.
     *
     * @return static Returns $this for method chaining
     */
    public function filterDirs(): Path;

    /**
     * Filter the stored paths to include only files.
     *
     * @return static Returns $this for method chaining
     */
    public function filterFiles(): Path;

    /**
     * Assert that all stored paths are valid files.
     *
     * Throws a RuntimeException for the first invalid path,
     * unless a custom exception handler is registered.
     *
     * @throws \RuntimeException
     * @return static Returns $this for method chaining
     */
    public function assertFile(): Path;

    /**
     * Assert that all stored paths are valid directories.
     *
     * Throws a RuntimeException for the first invalid path,
     * unless a custom exception handler is registered.
     *
     * @throws \RuntimeException
     * @return static Returns $this for method chaining
     */
    public function assertDir(): Path;

    /**
     * Add or set paths to the instance.
     *
     * Accepts a single path or an array of paths. Replaces existing paths if an array is provided.
     *
     * @param string|string[] $path Single path or array of paths
     * @return static Returns $this for method chaining
     */
    public function on(string|array $path): Path;
}
