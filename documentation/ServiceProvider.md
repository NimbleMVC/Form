# Klasa ServiceProvider

Dostawca usług dla biblioteki NimblePHP Form, odpowiedzialny za rejestrację i konfigurację komponentów formularzy w frameworku NimblePHP.

## Namespace

```php
NimblePHP\Form\ServiceProvider
```

## Implementuje

```php
NimblePHP\Framework\Interfaces\ServiceProviderInterface
```

## Przeznaczenie

`ServiceProvider` automatycznie:
- Kopiuje pliki JavaScript do katalogu publicznego
- Rejestruje skrypty w module Twig (jeśli dostępny)
- Konfiguruje środowisko dla działania formularzy

## Metody

### `register(): void`

Główna metoda rejestrująca usługi i konfigurującą środowisko.

**Wykonywane operacje:**
1. Kopiowanie pliku `form.js` do `public/assets/`
2. Rejestracja skryptu w module Twig (jeśli dostępny)

**Przykład:**
```php
public function register(): void
{
    // Kopiowanie pliku JavaScript
    File::copy(__DIR__ . '/Resources/form.js', Kernel::$projectPath . '/public/assets/form.js');

    // Rejestracja w module Twig
    if (ModuleRegister::moduleExistsInVendor('nimblephp/twig')) {
        try {
            Twig::addJsHeader('/assets/form.js');
        } catch (\Throwable) {
            // Cicha obsługa błędów
        }
    }
}
```

## Rejestracja w aplikacji

### Automatyczna rejestracja

ServiceProvider jest automatycznie rejestrowany przez framework NimblePHP podczas instalowania pakietu przez Composer.

### Manualna rejestracja

Jeśli potrzebujesz manualnie zarejestrować ServiceProvider:

```php
// config/services.php
use NimblePHP\Form\ServiceProvider as FormServiceProvider;

return [
    'providers' => [
        FormServiceProvider::class,
        // inne dostawcy usług...
    ]
];
```

### Rejestracja w bootstrap aplikacji

```php
// bootstrap/app.php
use NimblePHP\Form\ServiceProvider as FormServiceProvider;
use NimblePHP\Framework\Application;

$app = new Application();

// Rejestracja dostawcy usług
$app->register(new FormServiceProvider());

return $app;
```

## Funkcjonalność JavaScript

### Plik form.js

ServiceProvider kopiuje plik `form.js` zawierający funkcjonalność AJAX dla formularzy:

```javascript
// Przykładowa zawartość form.js
jQuery.fn.ajaxform = function() {
    return this.each(function() {
        var form = $(this);
        
        form.on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                success: function(response) {
                    // Obsługa odpowiedzi
                    form.html(response);
                },
                error: function() {
                    // Obsługa błędów
                    alert('Wystąpił błąd podczas wysyłania formularza');
                }
            });
        });
    });
};

// Automatyczna inicjalizacja formularzy AJAX
$(document).ready(function() {
    $('.ajax-form').ajaxform();
});
```

### Integracja z Twig

Jeśli w projekcie używany jest moduł `nimblephp/twig`, ServiceProvider automatycznie dodaje skrypt do nagłówka strony:

```twig
{# Automatycznie dodane przez ServiceProvider #}
<script src="/assets/form.js"></script>
```

## Przykłady użycia

### Podstawowa konfiguracja

```php
// config/app.php
<?php

use NimblePHP\Form\ServiceProvider as FormServiceProvider;

return [
    'providers' => [
        FormServiceProvider::class,
    ],
    
    // Inne konfiguracje...
];
```

### Konfiguracja z dodatkowymi ustawieniami

```php
// config/form.php
<?php

return [
    'default_method' => 'POST',
    'ajax_enabled' => true,
    'csrf_protection' => true,
    'auto_focus_errors' => true,
    
    'assets' => [
        'js_path' => '/assets/form.js',
        'css_path' => '/assets/form.css',
    ],
    
    'validation' => [
        'language' => 'PL',
        'show_errors_inline' => true,
    ]
];
```

### Rozszerzenie ServiceProvider

```php
<?php

namespace App\Providers;

use NimblePHP\Form\ServiceProvider as BaseFormServiceProvider;
use NimblePHP\Framework\Kernel;
use Krzysztofzylka\File\File;

class CustomFormServiceProvider extends BaseFormServiceProvider
{
    public function register(): void
    {
        // Wywołanie podstawowej rejestracji
        parent::register();
        
        // Dodatkowe konfiguracje
        $this->registerCustomAssets();
        $this->configureValidation();
        $this->setupFormTemplates();
    }
    
    private function registerCustomAssets(): void
    {
        // Kopiowanie niestandardowych stylów CSS
        File::copy(
            __DIR__ . '/../../resources/assets/custom-form.css',
            Kernel::$projectPath . '/public/assets/custom-form.css'
        );
        
        // Kopiowanie dodatkowych skryptów JS
        File::copy(
            __DIR__ . '/../../resources/assets/form-extensions.js',
            Kernel::$projectPath . '/public/assets/form-extensions.js'
        );
        
        // Rejestracja w Twig
        if (class_exists('NimblePHP\Twig\Twig')) {
            \NimblePHP\Twig\Twig::addCssHeader('/assets/custom-form.css');
            \NimblePHP\Twig\Twig::addJsHeader('/assets/form-extensions.js');
        }
    }
    
    private function configureValidation(): void
    {
        // Ustawienie domyślnego języka walidacji
        \NimblePHP\Form\Validation::changeLanguage('PL');
        
        // Dodanie niestandardowych komunikatów błędów
        \NimblePHP\Form\Validation::$language = array_merge(
            \NimblePHP\Form\Validation::$language,
            [
                'custom_rule' => 'Niestandardowy błąd walidacji',
                'strong_password' => 'Hasło musi zawierać co najmniej 8 znaków, wielką literę, małą literę i cyfrę'
            ]
        );
    }
    
    private function setupFormTemplates(): void
    {
        // Konfiguracja szablonów formularzy
        $templatesPath = Kernel::$projectPath . '/resources/views/forms';
        
        if (!is_dir($templatesPath)) {
            mkdir($templatesPath, 0755, true);
        }
        
        // Kopiowanie domyślnych szablonów
        $defaultTemplates = [
            'form_wrapper.twig',
            'field_wrapper.twig',
            'error_message.twig'
        ];
        
        foreach ($defaultTemplates as $template) {
            $sourcePath = __DIR__ . '/../../resources/templates/' . $template;
            $targetPath = $templatesPath . '/' . $template;
            
            if (file_exists($sourcePath) && !file_exists($targetPath)) {
                File::copy($sourcePath, $targetPath);
            }
        }
    }
}
```

### Użycie w kontrolerze

```php
<?php

namespace App\Controllers;

use NimblePHP\Framework\Controller;
use NimblePHP\Form\FormBuilder;

class ContactController extends Controller
{
    public function index(): string
    {
        // ServiceProvider automatycznie zapewnia dostępność
        // plików JavaScript i integrację z Twig
        
        $form = FormBuilder::generate('ContactFormBuilder', $this);
        
        return $this->render('contact/index.twig', [
            'form' => $form
        ]);
    }
}
```

### Template Twig z formularzem

```twig
{# views/contact/index.twig #}
{% extends 'layout.twig' %}

{% block content %}
<div class="container">
    <h1>Kontakt</h1>
    
    {# Formularz z automatyczną obsługą AJAX #}
    {{ form|raw }}
</div>

{# JavaScript zostanie automatycznie dołączony przez ServiceProvider #}
{% endblock %}
```

## Struktura plików

```
src/
├── Resources/
│   └── form.js              # Skrypt JavaScript dla formularzy
├── ServiceProvider.php      # Główny dostawca usług
└── ...

public/                      # Utworzone przez ServiceProvider
├── assets/
│   └── form.js             # Skopiowany skrypt JavaScript
└── ...
```

## Zależności

ServiceProvider wymaga następujących pakietów:

- `krzysztofzylka/file` - do kopiowania plików
- `nimblephp/framework` - podstawowy framework
- `nimblephp/twig` - opcjonalnie, dla integracji z Twig

## Obsługa błędów

ServiceProvider jest zaprojektowany tak, aby działał niezawodnie:

```php
public function register(): void
{
    try {
        // Kopiowanie pliku JavaScript
        File::copy(__DIR__ . '/Resources/form.js', Kernel::$projectPath . '/public/assets/form.js');
    } catch (\Exception $e) {
        // Logowanie błędu bez przerywania działania aplikacji
        error_log('FormServiceProvider: Failed to copy form.js - ' . $e->getMessage());
    }

    // Cicha obsługa integracji z Twig
    if (ModuleRegister::moduleExistsInVendor('nimblephp/twig')) {
        try {
            Twig::addJsHeader('/assets/form.js');
        } catch (\Throwable) {
            // Błędy są ignorowane - aplikacja działa dalej
        }
    }
}
```

## Konfiguracja środowiska

### Uprawnienia katalogów

Upewnij się, że katalog `public/assets/` ma odpowiednie uprawnienia:

```bash
chmod 755 public/assets/
```

### Serwer web

Skonfiguruj serwer web tak, aby serwował pliki statyczne z katalogu `public/`:

```nginx
# Nginx
location /assets/ {
    alias /path/to/project/public/assets/;
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

```apache
# Apache
<Directory "/path/to/project/public/assets">
    ExpiresActive On
    ExpiresDefault "access plus 1 year"
    Header append Cache-Control "public, immutable"
</Directory>
```

## Rozwiązywanie problemów

### Brak pliku form.js

```php
// Sprawdzenie czy plik został skopiowany
if (!file_exists(Kernel::$projectPath . '/public/assets/form.js')) {
    // Manualne kopiowanie
    $serviceProvider = new \NimblePHP\Form\ServiceProvider();
    $serviceProvider->register();
}
```

### Problemy z Twig

```php
// Sprawdzenie integracji z Twig
if (class_exists('NimblePHP\Twig\Twig')) {
    try {
        \NimblePHP\Twig\Twig::addJsHeader('/assets/form.js');
        echo "Integracja z Twig działa poprawnie";
    } catch (\Exception $e) {
        echo "Błąd integracji z Twig: " . $e->getMessage();
    }
}
```

## Zobacz również

- [Form](Form.md) - Główna klasa formularza
- [FormBuilder](FormBuilder.md) - Builder formularzy
- [Validation](Validation.md) - Walidacja danych
- Dokumentacja NimblePHP Framework
