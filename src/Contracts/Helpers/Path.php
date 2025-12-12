<?php

namespace Felora\Contracts\Helpers;

/**
 * Interface Path
 *
 * Contract for handling file and directory paths.
 * Provides methods to check existence and type of paths,
 * and to manage the paths list.
 *
 * @package Felora\Contracts\Helpers
 */
interface Path
{
    /**
     * Check if all paths are valid directories.
     *
     * @return bool True if all paths exist and are directories, false otherwise
     */
    public function isDir(): bool;

    /**
     * Check if all paths are valid files.
     *
     * @return bool True if all paths exist and are files, false otherwise
     */
    public function isFile(): bool;

    /**
     * Get all paths currently stored in the instance.
     *
     * @return string[] Array of paths
     */
    public function get(): array;

    /**
     * Add or set paths to the instance.
     *
     * @param string|string[] $path Single path or array of paths
     * @return static
     */
    public function on(string|array $path);
}
