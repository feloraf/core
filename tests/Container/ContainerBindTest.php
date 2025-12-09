<?php
declare(strict_types=1);

use Felora\Container\Container;
use Felora\Contracts\Container\Container as ContainerContract;
use Tests\TestCase;

class ContainerBindTest extends TestCase
{
    private ContainerContract $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new Container;
    }

    public function test_simple_closure_bind_and_resolve(): void
    {
        $this->container->bind('foo', function () {
            return 'bar';
        });

        $this->assertSame('bar', $this->container->make('foo'));
    }

    public function test_closure_bind_receives_container_and_can_resolve_dependencies(): void
    {
        $this->container->bind(Monolog::class, function () {
            return new Monolog();
        });

        $this->container->bind(Logging::class, function (ContainerContract $c) {
            return new Logging($c->make(Monolog::class));
        });

        $instance = $this->container->make(Logging::class);

        $this->assertInstanceOf(Logging::class, $instance);
        $this->assertInstanceOf(Monolog::class, $instance->getLogger());
    }

    public function test_bind_class_name_to_class_name_and_resolve(): void
    {
        $this->container->bind(Concrete::class);

        $a = $this->container->make(Concrete::class);
        $b = $this->container->make(Concrete::class);

        $this->assertInstanceOf(Concrete::class, $a);
        $this->assertInstanceOf(Concrete::class, $b);

        $this->assertNotSame($a, $b);
    }

    public function test_make_throws_exception_when_identifier_not_bound(): void
    {
        $this->expectException(Exception::class);

        $this->container->make('this_does_not_exist');
    }

    public function test_binding_can_be_overridden_by_later_bind_call(): void
    {
        $this->container->bind('value', function () {
            return 'first';
        });

        $this->assertSame('first', $this->container->make('value'));

        $this->container->bind('value', function () {
            return 'second';
        });

        $this->assertSame('second', $this->container->make('value'));
    }

    public function test_bind_interface_to_concrete_and_resolve_by_interface(): void
    {
        $this->container->bind(LoggerInterface::class, function () {
            return new Monolog();
        });

        $resolved = $this->container->make(LoggerInterface::class);

        $this->assertInstanceOf(LoggerInterface::class, $resolved);
        $this->assertInstanceOf(Monolog::class, $resolved);
    }

    public function test_closure_bind_receives_params_in_make_call(): void
    {
        $this->container->bind('sum', function (ContainerContract $c, array $params) {
            return $params['a'] + $params['b'];
        });

        $result = $this->container->make('sum', ['a' => 3, 'b' => 7]);

        $this->assertSame(10, $result);
    }

    public function test_closure_bind_params_are_optional_and_can_have_defaults(): void
    {
        $this->container->bind('greet', function (ContainerContract $c, array $params) {
            $name = $params['name'] ?? 'Guest';
            return "Hello, {$name}";
        });

        $this->assertSame('Hello, Ali', $this->container->make('greet', ['name' => 'Ali']));
        $this->assertSame('Hello, Guest', $this->container->make('greet'));
    }

    public function test_closure_bind_can_mix_auto_injection_and_params(): void
    {
        $this->container->bind(SimpleService::class);

        $this->container->bind('complex', function (ContainerContract $c, array $params) {
            $service = $c->make(SimpleService::class);
            return $service->say() . ' - ' . $params['extra'];
        });

        $result = $this->container->make('complex', ['extra' => 'done']);

        $this->assertSame('ok - done', $result);
    }

    public function test_closure_with_invalid_first_parameter_type_throws_exception(): void
    {
        $this->container->bind('bad', function (int $wrong) {
            return 'fail';
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The first parameter of the bind closure must be type-hinted');

        $this->container->make('bad');
    }

    public function test_closure_with_only_container_parameter_receives_only_container(): void
    {
        $this->container->bind('onlyContainer', function (ContainerContract $c) {
            return $c instanceof ContainerContract;
        });

        $this->assertTrue($this->container->make('onlyContainer'));
    }

    public function test_closure_with_no_typehint_on_first_parameter_is_allowed(): void
    {
        $this->container->bind('noType', function ($c) {
            return $c instanceof ContainerContract;
        });

        $this->assertTrue($this->container->make('noType'));
    }

    public function test_closure_with_extra_parameters_is_not_allowed(): void
    {
        $this->expectExceptionMessage("Too few arguments to function");

        $this->container->bind('extraParams', function ($c, array $params, SimpleService $service) {
            return $c instanceof ContainerContract;
        });

        $this->container->make('extraParams', ['name' => 'john doe']);
    }
}

interface LoggerInterface {}

class Monolog implements LoggerInterface
{
    //
}

class Logging
{
    public function __construct(private LoggerInterface $logger) {}

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}

class Concrete
{
    //
}

class SimpleService
{
    public function say(): string
    {
        return 'ok';
    }
}
