<?php

namespace Nimblephp\form;

use Krzysztofzylka\File\File;
use Nimblephp\framework\Interfaces\ServiceProviderInterface;
use Nimblephp\framework\Kernel;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(): void
    {
        File::copy(__DIR__ . '/Resources/form.js', Kernel::$projectPath . '/public/assets/form.js');
    }

}