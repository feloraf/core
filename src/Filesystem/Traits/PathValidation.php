<?php

namespace Felora\Filesystem\Traits;

use RuntimeException;
use Felora\Contracts\Filesystem\Path as PathContract;

trait PathValidation
{
    /**
     * Check if all paths are valid files.
     *
     * @return bool
     */
    public function isFile(): bool
    {
        return $this->all(fn ($path) => $this->hasFile($path));
    }

    /**
     * Check if all paths are valid directories.
     *
     * @return bool
     */
    public function isDir(): bool
    {
        return $this->all(fn ($path) => $this->hasDir($path));
    }

    /**
     * Assert that all given paths are valid files.
     *
     * If an exception handler callback is defined, it will be invoked
     * with the failing path instead of throwing an exception.
     *
     * @throws RuntimeException
     * @return PathContract
     */
    public function assertFile(): PathContract
    {
        return $this->assertAll(
            fn ($path) => $this->hasFile($path),
            "Path '%s' is not a valid file."
        );
    }

    /**
     * Assert that all given paths are valid directories.
     *
     * If an exception handler callback is defined, it will be invoked
     * with the failing path instead of throwing an exception.
     *
     * @throws RuntimeException
     * @return PathContract
     */
    public function assertDir(): PathContract
    {
        return $this->assertAll(
            fn ($path) => $this->hasDir($path),
            "Path '%s' is not a valid directory."
        );
    }

    /**
     * Determine whether all paths (direct or scanned) satisfy a given condition.
     *
     * @param callable $checker
     * @return bool
     */
    protected function all(callable $checker): bool
    {
        foreach ($this->paths as $path) {
            if (! $checker($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assert all paths using a given validator.
     *
     * @param callable $validator
     * @param string   $errorMessage
     * @return PathContract
     *
     * @throws RuntimeException
     */
    protected function assertAll(\Closure $checker, string $errorMessage): PathContract
    {
        $paths = $this->paths;

        foreach ($paths as $path) {
            if ($checker($path)) {

                continue;
            }

            if (is_callable($this->exception)) {
                ($this->exception)($path);

                continue;
            }

            throw new RuntimeException(sprintf($errorMessage, (string) $path));
        }

        return $this;
    }
}
