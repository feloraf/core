<?php

use Felora\Support\Arr\DotNotation;
use Tests\TestCase;

class DotNotationTest extends TestCase
{
    private DotNotation $dot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dot = new DotNotation();
    }

    public function test_sets_a_simple_key(): void
    {
        $array = [];
        $this->dot->set($array, 'app.name', 'Felora');

        $this->assertEquals([
            'app' => [
                'name' => 'Felora'
            ]
        ], $array);
    }

    public function test_overwrites_existing_value(): void
    {
        $array = ['app' => ['name' => 'OldName']];
        $this->dot->set($array, 'app.name', 'Felora');

        $this->assertEquals([
            'app' => [
                'name' => 'Felora'
            ]
        ], $array);
    }

    public function test_creates_nested_arrays_automatically(): void
    {
        $array = [];
        $this->dot->set($array, 'app.config.env', 'local');

        $this->assertEquals([
            'app' => [
                'config' => [
                    'env' => 'local'
                ]
            ]
        ], $array);
    }

    public function test_can_set_multiple_keys_independently(): void
    {
        $array = [];
        $this->dot->set($array, 'app.name', 'Felora');
        $this->dot->set($array, 'app.version', '1.0');

        $this->assertEquals([
            'app' => [
                'name' => 'Felora',
                'version' => '1.0'
            ]
        ], $array);
    }

    public function test_overwrites_nested_arrays_if_needed(): void
    {
        $array = ['app' => 'wrongValue'];
        $this->dot->set($array, 'app.name', 'Felora');

        $this->assertEquals([
            'app' => [
                'name' => 'Felora'
            ]
        ], $array);
    }

    /** Get method */

    public function test_get_simple_key(): void
    {
        $array = ['name' => 'felora'];

        $result = $this->dot->get($array, 'name');

        $this->assertEquals('felora', $result);
    }

    public function test_get_nested_key(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'runtime' => 'swoole',
                'path' => __DIR__,
            ],
        ];

        $result = $this->dot->get($array, 'app.runtime');

        $this->assertEquals('swoole', $result);
    }

    public function test_get_deep_nested_key(): void
    {
        $array = [
            'db' => [
                'connections' => [
                    'pgsql' => [
                        'port' => 5432,
                    ],
                    'mysql' => [
                        'port' => 3306,
                    ],
                    'mongodb' => [
                        'port' => 27017,
                    ],
                ],
            ],
        ];

        $result = $this->dot->get($array, 'db.connections.mysql.port');

        $this->assertEquals(3306, $result);
    }

    public function test_get_non_existent_key_returns_null(): void
    {
        $array = ['name' => 'felora'];

        $result = $this->dot->get($array, 'non.existent.key');

        $this->assertNull($result);
    }

    public function test_get_with_empty_array(): void
    {
        $array = [];

        $result = $this->dot->get($array, 'non.existent.key');

        $this->assertNull($result);
    }

    public function test_get_with_null_key(): void
    {
        $array = ['name' => 'felora'];

        $result = $this->dot->get($array, '');

        $this->assertNull($result);
    }

    /** Has method */

    public function test_has_returns_true_for_existing_key(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
            ],
        ];

        $this->assertTrue(
            $this->dot->has($array, 'app.name')
        );
    }

    public function test_has_returns_false_for_missing_key(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
            ],
        ];

        $this->assertFalse(
            $this->dot->has($array, 'app.runtime')
        );
    }

    public function test_has_returns_true_for_nested_key(): void
    {
        $array = [
            'db' => [
                'connections' => [
                    'mysql' => [
                        'port' => 3307,
                    ],
                ],
            ],
        ];

        $this->assertTrue(
            $this->dot->has($array, 'db.connections.mysql.port')
        );
    }

    public function test_has_returns_false_when_path_breaks(): void
    {
        $array = [
            'db' => [
                'connections' => [
                    'pgsql' => [
                        //
                    ],
                ],
            ],
        ];

        $this->assertFalse(
            $this->dot->has($array, 'db.connections.mysql')
        );
    }

    public function test_has_returns_false_for_empty_array(): void
    {
        $array = [];

        $this->assertFalse(
            $this->dot->has($array, 'app.runtime')
        );
    }

    /** Flatten method */

    public function test_flatten_simple_array(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'runtime' => 'swoole',
            ],
        ];

        $expected = [
            'app.name' => 'felora',
            'app.runtime' => 'swoole',
        ];

        $this->assertEquals($expected, $this->dot->flatten($array));
    }

    public function test_flatten_nested_array(): void
    {
        $array = [
            'db' => [
                'connections' => [
                    'mysql' => ['host' => '127.0.0.1', 'port' => 3306],
                    'pgsql' => ['host' => '0.0.0.0', 'port' => 5432],
                ],
            ],
        ];

        $expected = [
            'db.connections.mysql.host' => '127.0.0.1',
            'db.connections.mysql.port' => 3306,
            'db.connections.pgsql.host' => '0.0.0.0',
            'db.connections.pgsql.port' => 5432,
        ];

        $this->assertEquals($expected, $this->dot->flatten($array));
    }

    public function test_flatten_with_empty_array(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'paths' => [
                    //
                ],
            ],
        ];

        $expected = [
            'app.name' => 'felora',
            'app.paths' => [],
        ];

        $this->assertEquals($expected, $this->dot->flatten($array));
    }

    public function test_flatten_with_numeric_keys(): void
    {
        $array = [
            'ports' => [80, 443],
        ];

        $expected = [
            'ports.0' => 80,
            'ports.1' => 443,
        ];

        $this->assertEquals($expected, $this->dot->flatten($array));
    }

    public function test_flatten_empty_input(): void
    {
        $array = [];

        $expected = [];

        $this->assertEquals($expected, $this->dot->flatten($array));
    }

    public function test_flatten_deeply_nested(): void
    {
        $array = [
            'app' => [
                'connections' => [
                    'pgsql' => [
                        'port' => 5432,
                    ],
                ],
            ],
        ];

        $expected = [
            'app.connections.pgsql.port' => 5432,
        ];

        $this->assertEquals($expected, $this->dot->flatten($array));
    }

     public function test_flatten_with_null_and_object_value(): void
    {
        $array = [
            'default' => null,
            'runtime' => $std = new stdClass(),
            'apps' => [
                'first_app' => null,
            ],
        ];

        $expected = [
            'default' => null,
            'runtime' => $std,
            'apps.first_app' => null,
        ];

        $this->assertEquals($expected, $this->dot->flatten($array));
    }

    /** Keys method */

    public function test_keys_single_level_array(): void
    {
        $array = [
            'name' => 'felora',
            'runtime' => 'swoole',
            'path' => __DIR__,
        ];

        $expected = [
            'name',
            'runtime',
            'path',
        ];

        $this->assertEquals($expected, $this->dot->keys($array));
    }

    public function test_keys_nested_array(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'runtime' => 'swoole',
            ],
            'db' => [
                'connections' => [
                    'pgsql' => [
                        'port' => 5432,
                    ]
                ],
            ],
        ];

        $expected = [
            'app.name',
            'app.runtime',
            'db.connections.pgsql.port',
        ];

        $this->assertEquals($expected, $this->dot->keys($array));
    }

    public function test_keys_with_numeric_indexes(): void
    {
        $array = [
            'ports' => [
                3306,
                5432,
            ],
        ];

        $expected = [
            'ports.0',
            'ports.1',
        ];

        $this->assertEquals($expected, $this->dot->keys($array));
    }

    public function test_keys_with_empty_arrays(): void
    {
        $array = [
            'routes' => [],
            'config' => [
                'app' => [],
                'exception' => [],
            ],
        ];

        $expected = [
            'routes',
            'config.app',
            'config.exception',
        ];

        $this->assertEquals($expected, $this->dot->keys($array));
    }

    public function test_keys_with_empty_root_array(): void
    {
        $array = [];

        $this->assertEquals([], $this->dot->keys($array));
    }

    /** Values method */

    public function test_values_single_level_array(): void
    {
        $array = [
            'name' => 'felora',
            'runtime' => 'swoole',
            'debug' => true,
        ];

        $expected = [
            'felora',
            'swoole',
            true,
        ];

        $this->assertEquals($expected, $this->dot->values($array));
    }

    public function test_values_nested_array(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'runtime' => 'swoole',
            ],
            'db' => [
                'connection' => [
                    'pgsql' => [
                        'port' => 5432,
                    ]
                ]
            ],
        ];

        $expected = [
            'felora',
            'swoole',
            5432,
        ];

        $this->assertEquals($expected, $this->dot->values($array));
    }

    public function test_values_with_numeric_indexes(): void
    {
        $array = [
            'ports' => [
                3306,
                5432,
            ],
        ];

        $expected = [
            3306,
            5432,
        ];

        $this->assertEquals($expected, $this->dot->values($array));
    }

    public function test_values_with_empty_arrays(): void
    {
        $array = [
            'routes' => [],
            'config' => [
                'app' => [],
                'exception' => [],
            ],
        ];

        $expected = [
            [],
            [],
            [],
        ];

        $this->assertEquals($expected, $this->dot->values($array));
    }

    public function test_values_with_empty_root_array(): void
    {
        $array = [];

        $this->assertEquals([], $this->dot->values($array));
    }

    /** Forget method */

    public function test_forgot_removes_simple_key(): void
    {
        $array = [
            'name' => 'felora',
            'runtime' => 'swoole',
        ];

        $this->dot->forgot($array, 'runtime');

        $this->assertSame([
            'name' => 'felora',
        ], $array);
    }

    public function test_forgot_removes_nested_key(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'runtime' => 'swoole',
            ],
        ];

        $this->dot->forgot($array, 'app.runtime');

        $this->assertSame([
            'app' => [
                'name' => 'felora',
            ],
        ], $array);
    }

    public function test_forgot_ignores_missing_key(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
            ],
        ];

        $this->dot->forgot($array, 'app.runtime');

        $this->assertSame([
            'app' => [
                'name' => 'felora',
            ],
        ], $array);
    }

    public function test_forgot_with_single_level_wildcard(): void
    {
        $array = [
            'db' => [
                'mysql' => ['port' => 3306],
                'pgsql' => ['port' => 5432],
            ],
        ];

        $this->dot->forgot($array, 'db.*.port');

        $this->assertSame([
            'db' => [
                'mysql' => [],
                'pgsql' => [],
            ],
        ], $array);
    }

    public function test_forgot_with_root_wildcard(): void
    {
        $array = [
            'cache' => [
                'driver' => 'file'
            ],
            'app' => [
                'paths' => [
                    'first_app' => __DIR__,
                ]
            ],
        ];

        $this->dot->forgot($array, '*.paths');

        $this->assertSame([
            'cache' => ['driver' => 'file'],
            'app' => [],
        ], $array);
    }

    public function test_forgot_removes_entire_branch(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'runtime' => 'swoole',
            ],
            'cache' => [
                'driver' => 'file'
            ],
        ];

        $this->dot->forgot($array, 'app');

        $this->assertSame([
            'cache' => [
                'driver' => 'file',
            ],
        ], $array);
    }

    public function test_forgot_with_numeric_indexes(): void
    {
        $array = [
            'ports' => [
                3306,
                3307,
                5432
            ],
        ];

        $this->dot->forgot($array, 'ports.1');

        $this->assertSame([
            'ports' => [
                0 => 3306,
                2 => 5432,
            ],
        ], $array);
    }

    public function test_forgot_with_wildcard_on_numeric_array(): void
    {
        $array = [
            'ports' => [
                3306,
                5432,
            ],
        ];

        $this->dot->forgot($array, 'ports.*');

        $this->assertSame([
            'ports' => [],
        ], $array);
    }

    public function test_forgot_with_double_wildcard_does_not_remove_parent(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'paths' => [
                    'first_app' => __DIR__,
                    'second_app' => __DIR__,
                ],
                'runtime' => $std = new stdClass,
                'debug' => false,
            ],
        ];

        $this->dot->forgot($array, 'app.*.*');

        $this->assertSame([
           'app' => [
                'name' => 'felora',
                'paths' => [],
                'runtime' => $std,
                'debug' => false,
            ],
        ], $array);
    }
}
