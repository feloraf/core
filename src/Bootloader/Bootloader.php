<?php
namespace Felora\Bootloader;

use Felora\Bootloader\Traits\Registery;
use Felora\Container\Container;
use Felora\Contracts\Bootloader\BootloaderException;
use Felora\Contracts\Container\Container as ContainerContract;
use Felora\Contracts\Support\AppPaths;

class Bootloader
{
    use Registery;

    protected ContainerContract $container;

    public function __construct()
    {
        $this->container = new Container;

        $this->handle();
    }

    protected function setConfig(): string
    {
        throw new BootloaderException('Bootloader requires the "setConfig" method to be implemented.');
    }

    private function handle(): void
    {
        $this->register();

        /** @var Path $apps */
        $apps = $this->container->make(AppPaths::class);

        $entrypoints = array_map(fn($path) => $path . DIRECTORY_SEPARATOR . 'index.php', $apps->get());

        $entrypoints = $apps->on($entrypoints)
            ->exception(fn($file) => throw new BootloaderException("The file [{$file}] does not exist."))
            ->assertFile()
            ->get();

        pcntl_async_signals(true);

        $pids = [];

        pcntl_signal(SIGTERM, function () use (&$pids) {
            foreach ($pids as $pid) {
                if ($pid > 0) {
                    posix_kill($pid, SIGTERM);
                }
            }

            exit(0);
        });

        foreach ($entrypoints as $entrypoint) {
            $pid = pcntl_fork();

            if ($pid === -1) {
                throw new BootloaderException("Unable to fork process for entrypoint: [{$entrypoint}].");
            }

            if ($pid === 0) {
                require_once $entrypoint;
                exit(0);
            }

            $pids[] = $pid;
        }

        while (true) {
            sleep(1);
        }
    }

    private function configPath(): string
    {
        return $this->setConfig();
    }
}
