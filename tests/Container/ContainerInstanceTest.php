<?php
declare(strict_types=1);

use Felora\Container\Container as FeloraContainer;
use Felora\Contracts\Container\Container as ContainerContract;
use Tests\TestCase;

class ContainerInstanceTest extends TestCase
{
    private ContainerContract_ $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new Container;
    }

    public function test_can_bind_and_resolve_an_instance()
    {
        $object = new VideoService();
        $this->container->instance('my_object', $object);

        $resolved = $this->container->make('my_object');

        $this->assertSame($object, $resolved, 'Resolved instance should be the same as the bound instance.');
    }

    public function test_overwrites_existing_instance()
    {
        $firstObject = new stdClass();
        $secondObject = new stdClass();

        $this->container->instance('my_object', $firstObject);
        $this->container->instance('my_object', $secondObject);

        $resolved = $this->container->make('my_object');

        $this->assertSame($secondObject, $resolved, 'Newly bound instance should overwrite the previous one.');
    }

    public function test_can_bind_and_resolve_multiple_instances()
    {
        $object1 = new stdClass();
        $object2 = new stdClass();

        $this->container->instance('first', $object1);
        $this->container->instance('second', $object2);

        $this->assertSame($object1, $this->container->make('first'));
        $this->assertSame($object2, $this->container->make('second'));
    }

    public function test_returns_the_same_instance_every_time()
    {
        $object = new stdClass();
        $this->container->instance('singleton', $object);

        $firstCall = $this->container->make('singleton');
        $secondCall = $this->container->make('singleton');

        $this->assertSame($firstCall, $secondCall, 'The instance should be the same on multiple make calls.');
    }

    public function test_can_change_between_instance_and_singleton()
    {
        $podcastService = new PodcastService();

        $this->container->instance('service', $podcastService);
        $this->assertSame($podcastService, $this->container->make('service'));

        $this->container->singleton('service', fn() => new VideoService);
        $resolved = $this->container->make('service');

        $this->assertNotSame($podcastService, $resolved, 'Instance should be replaced by singleton.');
        $this->assertInstanceOf(VideoService::class, $resolved);

        $podcastService = new PodcastService();
        $this->container->instance('service', $podcastService);
        $this->assertSame($podcastService, $this->container->make('service'), 'Singleton should be replaced by instance.');
    }

    public function test_unsets_any_shared_instance_regardless_of_binding_type()
    {
        // --- podcast: normal bind then unset ---
        $this->container->bind('podcast', fn() => new PodcastService());
        $this->container->unsetSharedInstance('podcast');

        $this->assertTrue(
            $this->container->isBound('podcast'),
            'Binding should remain after unsetting shared instance.'
        );

        // --- video: singleton then unset ---
        $this->container->singleton('video', fn() => new VideoService());
        $this->container->unsetSharedInstance('video');

        $this->assertFalse(
            $this->container->isInstance('video'),
            'Video should no longer be considered an instance after unset.'
        );

        $this->assertFalse(
            $this->container->isShared('video'),
            'Video should not be marked as shared after unset.'
        );

        $this->assertNull(
            $this->container->getResolved('video'),
            'Resolved instance of video should be cleared.'
        );

        // --- std: bound instance then unset ---
        $this->container->instance('std', new stdClass());
        $this->container->unsetSharedInstance('std');

        $this->assertFalse(
            $this->container->isInstance('std'),
            'Std should no longer be considered an instance after unset.'
        );

        $this->assertFalse(
            $this->container->isShared('std'),
            'Std should not be marked as shared after unset.'
        );

        $this->assertNull(
            $this->container->getResolved('std'),
            'Resolved instance of std should be removed.'
        );
    }
}

interface ContainerContract_ extends ContainerContract
{
    public function isBound(string $abstract);

    public function isShared(string $abstract);

    public function isInstance(string $abstract);

    public function getResolved(string $abstract);

    public function unsetSharedInstance(string $abstract);
}

class Container extends FeloraContainer implements ContainerContract_
{
    public function isBound(string $abstract): bool
    {
        return parent::isBound($abstract);
    }

    public function isShared(string $abstract): bool
    {
        return parent::isShared($abstract);
    }

    public function isInstance($abstract): bool
    {
        return parent::isInstance($abstract);
    }

    public function getResolved(string $abstract): ?object
    {
        return parent::getResolved($abstract);
    }

    public function unsetSharedInstance($abstract): void
    {
        parent::unsetSharedInstance($abstract);
    }
}

class PodcastService {
    //
}

class VideoService {
    //
}
