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
}
