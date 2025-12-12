<?php

namespace Felora\Support;

use Exception;
use Felora\Contracts\Helpers\Path;
use Felora\Contracts\Container\Container;
use Felora\Contracts\Runtime\Runtime;
use Felora\Contracts\Support\ConfigManager as SupportManagerLoader;
use Felora\Contracts\Support\ConfigManagerException;
use ReflectionClass;

class ConfigManager implements SupportManagerLoader
{
    private $config;

    public function __construct(private string $configPath, private Container $container)
    {
        $this->manage();
    }

    private function manage(): void
    {
        $this->load();
        $this->runtime();
        $this->apps();
    }

    private function load(): void
    {
        if (! is_string($this->configPath)) {
            throw new ConfigManagerException("Config path must be a string.");
        }

        if (! file_exists($this->configPath)) {
            throw new ConfigManagerException("Config file does not exist at path: {$this->configPath}");
        }

        $this->config = (array)(require_once $this->configPath);
    }

    private function runtime(): void
    {
        if (! isset($this->config['runtime'])) {
            throw new ConfigManagerException("Missing 'runtime' key in configuration file.");
        }

        if (! is_string($this->config['runtime'])) {
            throw new ConfigManagerException("The runtime configuration value must be a string.");

        }

        $runtime = $this->config['runtime'];

        if (! class_exists($runtime)) {
            throw new ConfigManagerException("Runtime class '{$runtime}' does not exist.");
        }

        $reflection = new ReflectionClass($runtime);

        if (! $reflection->implementsInterface(\Felora\Contracts\Runtime\Runtime::class)) {
            throw new ConfigManagerException(
                "Runtime class '{$runtime}' must implement " . \Felora\Contracts\Runtime\Runtime::class . "."
            );
        }

        $this->container->singleton(Runtime::class, function () use($runtime) {
            return new $runtime;
        });
    }

    private function apps(): void
    {
        if(! isset($this->config['apps'])) {
            throw new ConfigManagerException();
        }

        if(! is_array($this->config['apps'])) {
            throw new ConfigManagerException();
        }

        /** @var \Felora\Contracts\Helpers\Path $path */
        $path = $this->container->make(Path::class);
        $apps = $this->config['apps'];
        switch ($apps) {
            case isset($apps['manual_paths']):
                $paths = $path->on(($apps['manual_paths']))
                    ->exception(fn($f) => throw new Exception($f))
                    ->_isDir()
                    ->instance();
                break;
            case isset($apps['paths']):
                $paths = $path->on(($apps['paths']))
                    ->exception(fn($f) => throw new Exception($f))
                    ->_isDir()
                    ->subPath(1)
                    ->instance();
                break;
            default:
                throw new Exception();
                break;

            $this->container->instance(App::class, $paths);
        }
    }
}
