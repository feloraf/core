<?php
declare(strict_types=1);

use Tests\TestCase;
use Felora\Helpers\Path\Path;
use Felora\Contracts\Helpers\Path\Path as PathContract;

class PathTest extends TestCase
{
    private PathContract $path;

    public function setUp(): void
    {
        parent::setUp();
        $this->path = new Path();
    }

    public function test_can_accept_single_path_and_detect_directory()
    {
        $dir = __DIR__;

        $this->path->on($dir);

        $this->assertTrue($this->path->isDir());
        $this->assertFalse($this->path->isFile());
    }

    public function test_can_accept_single_path_and_detect_file()
    {
        $file = __FILE__;

        $this->path->on($file);

        $this->assertTrue($this->path->isFile());
        $this->assertFalse($this->path->isDir());
    }

    public function test_returns_false_if_any_path_is_not_a_directory()
    {
        $validDir = __DIR__;
        $invalidPath = __DIR__ . '/not-existing-dir';

        $this->path->on([$validDir, $invalidPath]);

        $this->assertFalse($this->path->isDir());
    }

    public function test_returns_false_if_any_path_is_not_a_file()
    {
        $validFile = __FILE__;
        $invalidPath = __DIR__ . '/not-existing-file';

        $this->path->on([$validFile, $invalidPath]);

        $this->assertFalse($this->path->isFile());
    }

    public function test_get_method_returns_all_stored_paths()
    {
        $paths = [__DIR__, __FILE__];

        $this->path->on($paths);

        $this->assertEquals($paths, $this->path->get());
    }

    public function test_on_method_replaces_paths_when_array_is_given()
    {
        $original = [__DIR__];
        $new = [__FILE__];

        $this->path->on($original);
        $this->path->on($new);

        $this->assertEquals($new, $this->path->get());
    }

    public function test_on_method_appends_path_when_string_is_given()
    {
        $dir = __DIR__;
        $file = __FILE__;

        $this->path->on($dir);
        $this->path->on($file);

        $this->assertEquals([$dir, $file], $this->path->get());
    }

    public function test_assert_dir_and_assert_file_can_be_called_sequentially()
    {
        $dir = __DIR__;
        $file = __FILE__;

        $this->path->on([$dir]);
        $this->path->assertDir();

        $this->path->on([$file]);
        $this->path->assertFile();

        $this->assertTrue(true);
    }

    public function test_assert_file_throws_custom_exception_when_path_is_directory()
    {
        $dir = __DIR__;

        $this->expectException(FileException::class);

        $this->path->on([$dir]);
        $this->path->exception(fn ($path) => throw new FileException());
        $this->path->assertFile();
    }

    public function test_assert_dir_throws_custom_exception_when_path_is_file()
    {
        $file = __FILE__;

        $this->expectException(DirException::class);

        $this->path->on([$file]);
        $this->path->exception(fn ($path) => throw new DirException());
        $this->path->assertDir();
    }

    public function test_filter_dirs_keeps_only_directory_paths()
    {
        $paths = [__DIR__, __FILE__];

        $this->path->on($paths);
        $this->path->filterDirs();

        $this->assertEquals([__DIR__], $this->path->get());
    }

    public function test_filter_files_keeps_only_file_paths()
    {
        $paths = [__DIR__, __FILE__];

        $this->path->on($paths);
        $this->path->filterFiles();

        $this->assertEquals([__FILE__], array_values($this->path->get()));
    }
}

class DirException extends \Exception {};

class FileException extends \Exception {};
