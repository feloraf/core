<?php

namespace Tests\Path;

use Tests\TestCase;
use Felora\Filesystem\PathScanner;
use Felora\Contracts\Filesystem\PathIteratorException;
use SplFileInfo;

class PathScannerTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/felora_path_scanner';

        @mkdir($this->basePath);
        @mkdir($this->basePath . '/level1');
        @mkdir($this->basePath . '/level1/level2');

        file_put_contents($this->basePath . '/root.txt', 'root');
        file_put_contents($this->basePath . '/level1/level1.txt', 'level1');
        file_put_contents($this->basePath . '/level1/level2/level2.txt', 'level2');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->basePath);
        parent::tearDown();
    }

    public function test_throws_exception_if_path_is_not_a_directory(): void
    {
        $this->expectException(PathIteratorException::class);

        new PathScanner('/invalid/path');
    }

    public function test_returns_iterator_instance(): void
    {
        $scanner = new PathScanner($this->basePath);

        $iterator = $scanner->scan();

        $this->assertInstanceOf(\Iterator::class, $iterator);
    }

    public function test_scans_only_current_level(): void
    {
        $scanner = new PathScanner(
            $this->basePath,
            depth: 1,
            onlyCurrentLevel: true
        );

        $paths = iterator_to_array($scanner->scan());

        $this->assertNotEmpty($paths);

        foreach ($paths as $item) {
            $this->assertInstanceOf(SplFileInfo::class, $item);
            $this->assertSame(
                1,
                substr_count($item->getPathname(), DIRECTORY_SEPARATOR)
                - substr_count($this->basePath, DIRECTORY_SEPARATOR)
            );
        }
    }

    public function test_scans_recursively_when_only_current_level_is_false(): void
    {
        $scanner = new PathScanner(
            $this->basePath,
            depth: 2,
            onlyCurrentLevel: false
        );

        $paths = iterator_to_array($scanner->scan());

        $this->assertGreaterThan(2, count($paths));
    }

    /**
     * Helper method to delete directories recursively.
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            is_dir($path)
                ? $this->deleteDirectory($path)
                : unlink($path);
        }

        rmdir($dir);
    }
}
