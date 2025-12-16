<?php

namespace Felora\Support;

use Felora\Contracts\Container\Container;
use Felora\Contracts\Helpers\Path\Path;
use Felora\Contracts\Runtime\Runtime;
use Felora\Contracts\Support\AppPaths;
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
        if (! isset($this->config['apps'])) {
            throw new ConfigManagerException("Missing 'apps' key in configuration file.");
        }

        if (! is_array($this->config['apps'])) {
            throw new ConfigManagerException("'apps' configuration must be an array.");
        }

        /** @var Path $path */
        $path = $this->container->make(Path::class);
        $apps = $this->config['apps'];

        if (isset($apps['manual_paths'])) {
            $pathInstance = $path->on($apps['manual_paths'])
                                ->filterDirs();
        } elseif (isset($apps['paths'])) {
            $pathInstance = $path->on($apps['paths'])
                                ->subPath(1, true)
                                ->filterDirs();
        } else {
            throw new ConfigManagerException(
                "The 'apps' configuration must contain either 'manual_paths' or 'paths' key."
            );
        }

        $this->container->instance(AppPaths::class, $pathInstance);
    }
}
