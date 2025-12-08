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

    public function test_closure_with_typehinted_container_parameter_is_supported(): void
    {
        $this->container->bind(Monolog::class, function () {
            return new Monolog();
        });

        $this->container->bind(ServiceUsingMonolog::class, function (ContainerContract $c) {
            return new ServiceUsingMonolog($c->make(Monolog::class));
        });

        $svc = $this->container->make(ServiceUsingMonolog::class);

        $this->assertInstanceOf(ServiceUsingMonolog::class, $svc);
        $this->assertInstanceOf(Monolog::class, $svc->logger());
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

class ServiceUsingMonolog
{
    private Monolog $logger;

    public function __construct(Monolog $logger)
    {
        $this->logger = $logger;
    }

    public function logger(): Monolog
    {
        return $this->logger;
    }
}

class SimpleService
{
    public function say(): string
    {
        return 'ok';
    }
}
