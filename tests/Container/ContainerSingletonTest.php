<?php
declare(strict_types=1);

use Felora\Container\Container;
use Felora\Contracts\Container\ContainerContracts;
use Tests\TestCase;

class ContainerSingletonTest extends TestCase
{
    private ContainerContracts $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new Container;
    }

    public function test_singleton_returns_same_instance_every_time(): void
    {
        $this->container->singleton(SimpleService::class, function () {
            return new SimpleService;
        });

        $a = $this->container->make(SimpleService::class);
        $b = $this->container->make(SimpleService::class);

        $this->assertInstanceOf(SimpleService::class, $a);
        $this->assertInstanceOf(SimpleService::class, $b);

        $this->assertSame($a, $b);
    }

    public function test_singleton_without_concrete_uses_abstract_as_concrete(): void
    {
        $this->container->singleton(Concrete::class);

        $a = $this->container->make(Concrete::class);
        $b = $this->container->make(Concrete::class);

        $this->assertSame($a, $b);
    }

    public function test_singleton_closure_is_executed_only_once(): void
    {
        $counter = 0;

        $this->container->singleton('counter_test', function () use (&$counter) {
            $counter++;
            return new stdClass();
        });

        $this->container->make('counter_test');
        $this->container->make('counter_test');
        $this->container->make('counter_test');

        // closure باید فقط یکبار اجرا شده باشد
        $this->assertEquals(1, $counter);
    }

    public function test_singleton_can_be_overridden(): void
    {
        $this->container->singleton('foo', function () {
            return (object)['value' => 'first'];
        });

        $first = $this->container->make('foo');

        // override
        $this->container->singleton('foo', function () {
            return (object)['value' => 'second'];
        });

        $second = $this->container->make('foo');

        $this->assertSame('second', $second->value);
        $this->assertNotSame($first, $second);
    }

    public function test_singleton_with_dependencies_is_resolved_correctly(): void
    {
        $this->container->bind(LoggerInterface::class, function () {
            return new Monolog();
        });

        $this->container->singleton(Logging::class, function ($c) {
            return new Logging($c->make(LoggerInterface::class));
        });

        $a = $this->container->make(Logging::class);
        $b = $this->container->make(Logging::class);

        $this->assertSame($a, $b);
        $this->assertInstanceOf(Monolog::class, $a->getLogger());
    }
}
