<?php

namespace Nimblephp\form;

use DebugBar\DataCollector\MessagesCollector;
use Krzysztofzylka\File\File;
use Nimblephp\debugbar\Collectors\ModuleCollector;
use Nimblephp\debugbar\Debugbar;
use Nimblephp\framework\Interfaces\ServiceProviderInterface;
use Nimblephp\framework\Kernel;
use Nimblephp\framework\ModuleRegister;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(): void
    {
        File::copy(__DIR__ . '/Resources/form.js', Kernel::$projectPath . '/public/assets/form.js');
    }

}