<?php

namespace Felora\Filesystem\Traits;

use Felora\Contracts\Filesystem\Path as PathContract;

/**
 * Trait PathFilter
 *
 * Provides convenient methods to filter the stored paths array
 * to include only directories or only files.
 *
 * @package Felora\Filesystem\Traits
 */
trait PathFiltering
{
    /**
     * Filter stored paths to only include directories.
     *
     * @return PathContract Returns $this for chaining
     */
    public function filterDirs(): PathContract
    {
        $this->filter($this->paths, fn($path) => $this->hasDir($path));

        return $this;
    }

    /**
     * Filter stored paths to only include files.
     *
     * @return PathContract Returns $this for chaining
     */
    public function filterFiles(): PathContract
    {
        $this->filter($this->paths, fn($path) => $this->hasFile($path));

        return $this;
    }

    /**
     * Apply a custom filter callback to the given paths array
     * and replace the internal paths with the filtered result.
     *
     * This method clears the current paths and re-adds only the
     * paths that pass the given callback.
     *
     * @param string[] $paths    List of paths to be filtered
     * @param \Closure(string): bool $callback
     *        Callback that receives a path and returns true
     *        if it should be kept, false otherwise.
     *
     * @return void
     */
    protected function filter(array $paths, \Closure $callback): void
    {
        $this->clean();
        $filter = array_filter($paths, $callback);
        $this->add($filter);
    }
}
