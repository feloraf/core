<?php

namespace Felora\Filesystem;

use CallbackFilterIterator;
use Iterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RecursiveCallbackFilterIterator;
use Felora\Contracts\Filesystem\PathIteratorException;
use SplFileInfo;

/**
 * Class PathScanner
 *
 * Helper class to recursively scan directories and files with depth and level control.
 *
 * Provides options to scan only files, only directories, or both, and to restrict scanning
 * to a specific depth or current level.
 *
 * @package Felora\Filesystem
 */
class PathScanner
{
    /** @var string Base path to start scanning from */
    private string $basePath;

    /** @var int Base depth of the path for relative depth calculation */
    private int $baseDepth;

    /**
     * PathScanner constructor.
     *
     * @param string $path Base path to scan
     * @param int $depth Maximum depth to scan (default 1)
     * @param bool $onlyCurrentLevel If true, only return items at exact depth
     *
     * @throws PathIteratorException If base path is not a directory
     */
    public function __construct(
        string $path,
        private int $depth = 1,
        private bool $onlyCurrentLevel = true
    ) {
        $this->basePath  = rtrim($path, DIRECTORY_SEPARATOR);
        $this->baseDepth = substr_count($this->basePath, DIRECTORY_SEPARATOR);

        if (!is_dir($this->basePath)) {
            throw new PathIteratorException("Base path '{$path}' is not a valid directory.");
        }
    }

    /**
     * Scan the directory recursively according to depth and level options.
     *
     * @return Iterator Returns an iterator of SplFileInfo objects
     */
    public function scan(): Iterator
    {
        $directoryIterator = new RecursiveDirectoryIterator(
            $this->basePath,
            RecursiveDirectoryIterator::SKIP_DOTS
        );

        // Filter to control traversal based on depth and current level
        $filteredIterator = new RecursiveCallbackFilterIterator(
            $directoryIterator,
            $this->traversalFilter()
        );

        $iteratorIterator = new RecursiveIteratorIterator(
            $filteredIterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        $iteratorIterator->setMaxDepth($this->depth);

        // If onlyCurrentLevel is true, wrap in a final filter for output
        if ($this->onlyCurrentLevel) {
            return new CallbackFilterIterator(
                $iteratorIterator,
                fn(SplFileInfo $current) => $iteratorIterator->getDepth() === $this->depth - 1
            );
        }

        return $iteratorIterator;
    }

    /**
     * Creates a closure for filtering traversal and preventing unnecessary recursion.
     *
     * @return \Closure
     */
    protected function traversalFilter(): \Closure
    {
        return function ($current) {
            if (! $current instanceof SplFileInfo) {
                return false;
            }

            $currentDepth = $this->relativeDepth($current->getRealPath());

            // Always allow directories to be traversed
            if ($current->isDir()) {
                return $currentDepth <= $this->depth;
            }

            // Files: allow only up to depth
            return $currentDepth <= $this->depth;
        };
    }

    /**
     * Calculate the relative depth of a path with respect to basePath.
     *
     * @param string $path
     * @return int Relative depth
     */
    protected function relativeDepth(string $path): int
    {
        return substr_count($path, DIRECTORY_SEPARATOR) - $this->baseDepth;
    }
}
