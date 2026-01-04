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
                'debug' => false,
                'runtime' => 'swoole',
                'name' => 'felora',
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
                    'mariadb' => [
                        'port' => 3307,
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
        $array = ['foo' => 'bar'];

        $result = $this->dot->get($array, 'non.existent.key');

        $this->assertNull($result);
    }

    public function test_get_with_empty_array(): void
    {
        $array = [];

        $result = $this->dot->get($array, 'any.key');

        $this->assertNull($result);
    }

    public function test_get_with_null_key(): void
    {
        $array = ['foo' => 'bar'];

        $result = $this->dot->get($array, '');

        $this->assertNull($result);
    }

    /** Has method */

    public function test_has_returns_true_for_existing_key(): void
    {
        $array = [
            'app' => [
                'runtime' => 'swoole',
            ],
        ];

        $this->assertTrue(
            $this->dot->has($array, 'app.runtime')
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
                        'host' => '127.0.0.1',
                    ],
                ],
            ],
        ];

        $this->assertTrue(
            $this->dot->has($array, 'db.connections.mysql.host')
        );
    }

    public function test_has_returns_false_when_path_breaks(): void
    {
        $array = [
            'db' => [
                'connections' => 'invalid',
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
                'version' => '1.0',
            ],
        ];

        $expected = [
            'app.name' => 'felora',
            'app.version' => '1.0',
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
            'types' => [
                'string' => 'hello',
                'empty_array' => [],
            ],
        ];

        $expected = [
            'types.string' => 'hello',
            'types.empty_array' => [],
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
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 42,
                    ],
                ],
            ],
        ];

        $expected = [
            'level1.level2.level3.value' => 42,
        ];

        $this->assertEquals($expected, $this->dot->flatten($array));
    }

    /** Keys method */

    public function test_keys_single_level_array(): void
    {
        $array = [
            'name' => 'felora',
            'version' => '1.0',
            'debug' => true,
        ];

        $expected = [
            'name',
            'version',
            'debug',
        ];

        $this->assertEquals($expected, $this->dot->keys($array));
    }

    public function test_keys_nested_array(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'env' => 'local',
            ],
            'db' => [
                'port' => 3306,
            ],
        ];

        $expected = [
            'app.name',
            'app.env',
            'db.port',
        ];

        $this->assertEquals($expected, $this->dot->keys($array));
    }

    public function test_keys_with_numeric_indexes(): void
    {
        $array = [
            'ports' => [
                8893,
                9054,
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
            'cache' => [],
            'config' => [
                'paths' => [],
            ],
        ];

        $expected = [
            'cache',
            'config.paths',
        ];

        $this->assertEquals($expected, $this->dot->keys($array));
    }

    public function test_keys_with_mixed_value_types(): void
    {
        $array = [
            'string' => 'hello',
            'null' => null,
            'object' => new stdClass(),
            'array' => [
                'nested' => null,
            ],
        ];

        $expected = [
            'string',
            'null',
            'object',
            'array.nested',
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
            'version' => '1.0',
            'debug' => true,
        ];

        $expected = [
            'felora',
            '1.0',
            true,
        ];

        $this->assertEquals($expected, $this->dot->values($array));
    }

    public function test_values_nested_array(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'env' => 'local',
            ],
            'db' => [
                'port' => 3306,
            ],
        ];

        $expected = [
            'felora',
            'local',
            3306,
        ];

        $this->assertEquals($expected, $this->dot->values($array));
    }

    public function test_values_with_numeric_indexes(): void
    {
        $array = [
            'ports' => [
                8893,
                9054,
            ],
        ];

        $expected = [
            8893,
            9054,
        ];

        $this->assertEquals($expected, $this->dot->values($array));
    }

    public function test_values_with_empty_arrays(): void
    {
        $array = [
            'cache' => [],
            'config' => [
                'paths' => [],
            ],
        ];

        $expected = [
            [],
            [],
        ];

        $this->assertEquals($expected, $this->dot->values($array));
    }

    public function test_values_with_mixed_value_types(): void
    {
        $object = new stdClass();

        $array = [
            'string' => 'hello',
            'null' => null,
            'object' => $object,
            'array' => [
                'nested' => null,
            ],
        ];

        $expected = [
            'hello',
            null,
            $object,
            null,
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
            'version' => '1.0',
        ];

        $this->dot->forgot($array, 'version');

        $this->assertSame([
            'name' => 'felora',
        ], $array);
    }

    public function test_forgot_removes_nested_key(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'env' => 'local',
            ],
        ];

        $this->dot->forgot($array, 'app.env');

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

        $this->dot->forgot($array, 'app.debug');

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
            'cache' => ['driver' => 'file'],
            'db' => ['port' => 3306],
        ];

        $this->dot->forgot($array, '*.port');

        $this->assertSame([
            'cache' => ['driver' => 'file'],
            'db' => [],
        ], $array);
    }

    public function test_forgot_removes_entire_branch(): void
    {
        $array = [
            'app' => [
                'name' => 'felora',
                'env' => 'local',
            ],
            'db' => [
                'port' => 3306,
            ],
        ];

        $this->dot->forgot($array, 'app');

        $this->assertSame([
            'db' => [
                'port' => 3306,
            ],
        ], $array);
    }

    public function test_forgot_with_numeric_indexes(): void
    {
        $array = [
            'ports' => [8893, 9054, 7777],
        ];

        $this->dot->forgot($array, 'ports.1');

        $this->assertSame([
            'ports' => [
                0 => 8893,
                2 => 7777,
            ],
        ], $array);
    }

    public function test_forgot_with_wildcard_on_numeric_array(): void
    {
        $array = [
            'ports' => [
                8893,
                9054,
            ],
        ];

        $this->dot->forgot($array, 'ports.*');

        $this->assertSame([
            'ports' => [],
        ], $array);
    }

    public function test_x(): void
    {
        $array = [
            'ports' => [
                8893,
                'ports' => [
                    8080,
                    2020
                ],
                9054,
            ],
        ];

        $this->dot->forgot($array, 'ports.*.*');

        $this->assertSame([
            'ports' => [
                8893,
                'ports' => [],
                9054,
            ],
        ], $array);
    }


}
