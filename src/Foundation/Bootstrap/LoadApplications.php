<?php

namespace Felora\Foundation\Bootstrap;

use Felora\Contracts\App\AppPaths;
use Felora\Contracts\Bootloader\BootloaderException;
use Felora\Contracts\Container\Container;
use Felora\Contracts\Foundation\Bootstrap\Bootstrap;

class LoadApplications implements Bootstrap
{
    public function bootstrap(Container $container): void
    {
        /** @var Path $apps */
        $apps = $container->make(AppPaths::class);

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
}
