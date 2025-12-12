<?php

namespace NimblePHP\Form;

use Krzysztofzylka\File\File;
use NimblePHP\Framework\Interfaces\ServiceProviderInterface;
use NimblePHP\Framework\Kernel;
use NimblePHP\Framework\ModuleRegister;
use NimblePHP\Twig\Twig;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(): void
    {
        if (filter_var($_ENV['FORM_COPY_ASSET'] ?? 'true', FILTER_VALIDATE_BOOLEAN)) {
        File::copy(__DIR__ . '/Resources/form.js', Kernel::$projectPath . '/public/assets/form.js');

        if (ModuleRegister::moduleExistsInVendor('nimblephp/twig')) {
            try {
                Twig::addJsHeader('/assets/form.js');
            } catch (\Throwable) {
            }
        }
        }
    }

}