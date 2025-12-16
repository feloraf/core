<?php

namespace Felora\Helpers\Path\Traits;

use Felora\Contracts\Helpers\Path\Path as PathContract;

/**
 * Trait PathFilter
 *
 * Provides convenient methods to filter the stored paths array
 * to include only directories or only files.
 *
 * @package Felora\Helpers\Path\Traits
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
        $paths = $this->paths;
        $this->clean();

        $this->add(array_filter($paths, fn($path) => $this->hasDir($path)));

        return $this;
    }

    /**
     * Filter stored paths to only include files.
     *
     * @return PathContract Returns $this for chaining
     */
    public function filterFiles(): PathContract
    {
        $paths = $this->paths;
        $this->clean();

        $this->add(array_filter($paths, fn($path) => $this->hasFile($path)));

        return $this;
    }
}
