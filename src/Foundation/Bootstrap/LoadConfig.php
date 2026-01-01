<?php

namespace Felora\Foundation\Bootstrap;

use Felora\Contracts\Bootloader\BootloaderException;
use Felora\Contracts\Container\Container;
use Felora\Contracts\Filesystem\Path;
use Felora\Contracts\Foundation\Bootstrap\Bootstrap;
use Felora\Contracts\Support\ConfigManagerException;
use Felora\Contracts\Support\ConfigManager as ConfigManagerContract;
use Felora\Support\ConfigManager;
use Felora\Support\PathResolver;

class LoadConfig implements Bootstrap
{
    public function bootstrap(Container $container): void
    {
        /** @var PathResolver $pathResolver */
        $pathResolver = $container->make(PathResolver::class);
        $configPath = $pathResolver->config();
        /** @var Path $path */
        $path = $container->make(Path::class);
        $x = $path->on($configPath)->subPath(1)->get();

        // dd($configFiles);
        // foreach($configFiles as $configFile) {
        //     $key = rtrim(basename($configFile), '.php');
        //     // $config = require_once $configFile;

        //     // Collection::doting($config);
        //     // Collection::x($key, $config);
        // }

        try {
            $configManager = new ConfigManager($configPath.'/app.php', $container);
        } catch (ConfigManagerException $e) {
            throw new BootloaderException($e->getMessage());
        }
// 
        $container->instance(ConfigManagerContract::class, $configManager);
    }

    private $_prefix = "";

    public function x(array $array, &$output) {
        foreach($array as $key => $item) {
            if(is_array($item)) {
                $this->_prefix = $this->_prefix.'.'.$key;
                $this->x($item, $output);
            } else {
                $output[$this->_prefix.'.'.$key] = $item;
            }
        }
    }
}
