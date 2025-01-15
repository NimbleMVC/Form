<?php

namespace Nimblephp\form;

use Krzysztofzylka\File\File;
use Nimblephp\framework\Interfaces\ServiceProviderInterface;
use Nimblephp\framework\Kernel;
use Nimblephp\framework\ModuleRegister;
use Nimblephp\twig\Twig;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(): void
    {
        File::copy(__DIR__ . '/Resources/form.js', Kernel::$projectPath . '/public/assets/form.js');

        if (ModuleRegister::moduleExistsInVendor('nimblephp/twig')) {
            try {
                Twig::addJsHeader('/assets/form.js');
            } catch (\Throwable) {
            }
        }
    }

}