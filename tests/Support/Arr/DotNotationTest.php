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
                'runtime' => 'swoole',
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
                    'mysql' => [
                        'port' => 3306,
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

    public function test_get_with_wildcard(): void
    {
        $array = [
            'db' => [
                'connections' => [
                    'mysql' => [
                            'abas' => [
                                'ahmad' => 'reza',
                            ],
                        'port' => 3306
                    ],
                    'pgsql' => [
                            'aa' => [
                                'ahmad' => 'reza',
                            ],
                        'port' => 5432
                    ],
                ],
            ],
        ];

        $result = $this->dot->get($array, 'db.connections.*.*.ahmad');
        dd($result);

        $this->assertEquals([3306, 5432], $result);
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
}