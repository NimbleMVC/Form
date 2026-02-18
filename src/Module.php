<?php

namespace NimblePHP\Form;

use Krzysztofzylka\File\File;
use NimblePHP\Framework\Config;
use NimblePHP\Framework\Kernel;
use NimblePHP\Framework\Module\Interfaces\ModuleInterface;
use NimblePHP\Framework\Module\ModuleRegister;
use NimblePHP\Framework\Translation\Translation;
use NimblePHP\Framework\Translation\TranslationProviderInterface;
use NimblePHP\Twig\Twig;

class Module implements ModuleInterface, TranslationProviderInterface
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

    /**
     * @return void
     */
    public function registerTranslations(): void
    {
        Translation::getInstance()->addTranslationPath(__DIR__ . '/Lang', Translation::PRIORITY_MODULE);
    }

}