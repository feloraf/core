<?php

use Felora\Support\Arr\DotNotation;
use Tests\TestCase;

class WildcardTest extends TestCase
{
    private DotNotation $dot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dot = new DotNotation();
    }

    /** Get method */

     public function test_get_with_wildcard(): void
    {
        $array = [
            'db' => [
                'connections' => [
                    'mysql' => [
                        'host' => '127.0.0.1',
                        'port' => 3306,
                    ],
                    'pgsql' => [
                        'host' => '0.0.0.0',
                        'port' => 5432,
                    ],
                ],
            ],
        ];

        $expected_ports = [3306, 5432];
        $expected_flattened_values = [
            '127.0.0.1',
            3306,
            '0.0.0.0',
            5432,
        ];

        $this->assertEquals(
            $expected_ports,
            $this->dot->get($array, 'db.connections.*.port')
        );

        $this->assertEquals(
            $expected_ports,
            $this->dot->get($array, 'db.*.*.port')
        );

        $this->assertEquals(
            $expected_flattened_values,
            $this->dot->get($array, 'db.*.*.*')
        );

        $this->assertEquals(
            $expected_flattened_values,
            $this->dot->get($array, '*.*.*.*')
        );
    }

    public function test_get_with_wildcard_preserves_all_value_types(): void
    {
        $object = new \stdClass();

        $array = [
            'types' => [
                'string' => 'hello world',
                'object' => $object,
                'nullable' => null,
                'empty_array' => [],
                'nested_array' => [
                    [null],
                    ['name' => 'Ahmadreza'],
                    [],
                ],
                'integer' => 12,
            ],
        ];

        $expected = [
            'hello world',
            $object,
            null,
            [],
            [
                [null],
                ['name' => 'Ahmadreza'],
                [],
            ],
            12,
        ];

        $this->assertEquals(
            $expected,
            $this->dot->get($array, 'types.*')
        );
    }

    public function test_get_wildcard_ignores_non_array_values(): void
    {
        $array = [
            'app' => [
                'runtime' => 'swoole',
                'ports' => [
                    8893,
                    9054,
                ],
                'db' => [
                    'mysql' => [
                        'port' => 3306,
                    ],
                    'pgsql' => [
                        'port' => 5432,
                    ],
                ],
                'debug' => false,
            ],
        ];

        $expected = [
            8893,
            9054,
            [
                'port' => 3306,
            ],
            [
                'port' => 5432,
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->dot->get($array, 'app.*.*')
        );
    }

    public function test_get_returns_empty_array_when_wildcard_path_not_found(): void
    {
        $array = [
            'db' => [
                'connections' => [
                    'mysql' => ['port' => 3306],
                    'pgsql' => ['port' => 5432],
                ],
            ],
        ];

        $result = $this->dot->get($array, 'db.*.mongodb');

        $this->assertEmpty($result);
    }

    /** Has method */

    public function test_has_with_wildcard_returns_true_if_any_match_exists(): void
    {
        $array = [
            'db' => [
                'connections' => [
                    'mysql' => ['port' => 3306],
                    'pgsql' => ['port' => 5432],
                ],
            ],
        ];

        $this->assertTrue(
            $this->dot->has($array, 'db.connections.*.port')
        );
    }

    public function test_has_with_wildcard_returns_false_if_no_match_exists(): void
    {
        $array = [
            'db' => [
                'connections' => [
                    'mysql' => ['host' => '127.0.0.1'],
                ],
            ],
        ];

        $this->assertFalse(
            $this->dot->has($array, 'db.*.*.port')
        );
    }
}