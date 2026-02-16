<?php

namespace NimblePHP\Form;

use Krzysztofzylka\File\File;
use NimblePHP\Framework\Config;
use NimblePHP\Framework\Interfaces\ServiceProviderInterface;
use NimblePHP\Framework\Kernel;
use NimblePHP\Framework\Module\Interfaces\ModuleInterface;
use NimblePHP\Framework\Module\ModuleRegister;
use NimblePHP\Twig\Twig;

class Module implements ModuleInterface
{

    public function getName(): string
    {
        return 'Nimblephp Forms';
    }

    public function register(): void
    {
        if (Config::get('FORM_COPY_ASSET', true)) {
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