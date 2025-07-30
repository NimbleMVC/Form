# Dokumentacja NimblePHP Form

Biblioteka **NimblePHP Form** dostarcza kompleksowe narzędzia do tworzenia i walidacji formularzy w aplikacjach PHP. Zaprojektowana z myślą o prostocie, umożliwia łatwe generowanie dynamicznych formularzy oraz ich validowanie.

## Spis treści

1. [Instalacja](#instalacja)
2. [Szybki start](#szybki-start)
3. [Klasy i interfejsy](#klasy-i-interfejsy)
4. [Przykłady użycia](#przykłady-użycia)
5. [Walidacja](#walidacja)
6. [Konfiguracja](#konfiguracja)

## Instalacja

```bash
composer require nimblephp/form
```

## Szybki start

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Enum\MethodEnum;

// Tworzenie prostego formularza
$form = new Form('/submit', MethodEnum::POST);
$form->setId('my-form');

// Dodawanie pól
$form->addInput('name', 'Imię')
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addTextarea('message', 'Wiadomość')
     ->addSubmitButton('Wyślij');

// Walidacja
$form->validation([
    'name' => ['required'],
    'email' => ['required', 'isEmail'],
    'message' => ['required', 'length' => ['min' => 10]]
]);

// Sprawdzenie czy formularz został wysłany
if ($form->onSubmit()) {
    $data = $form->getData();
    // Przetwarzanie danych...
}

// Renderowanie
echo $form->render();
```

## Klasy i interfejsy

### Główne klasy
- [Form](Form.md) - Główna klasa do tworzenia formularzy
- [FormBuilder](FormBuilder.md) - Abstrakcyjna klasa do budowania formularzy
- [Validation](Validation.md) - Klasa do walidacji danych formularza
- [ServiceProvider](ServiceProvider.md) - Dostawca usług dla frameworka

### Enumy
- [MethodEnum](MethodEnum.md) - Enum definiujący metody HTTP dla formularzy

### Wyjątki
- [ValidationException](ValidationException.md) - Wyjątek rzucany podczas błędów walidacji

### Interfejsy
- [FormBuilderInterface](FormBuilderInterface.md) - Interfejs dla builderów formularzy

### Traity
- [Field](Field.md) - Trait do zarządzania polami formularza
- [Helpers](Helpers.md) - Trait z funkcjami pomocniczymi
- [Validation](ValidationTrait.md) - Trait do walidacji formularzy

## Przykłady użycia

### Prosty formularz kontaktowy

```php
$form = new Form();
$form->setId('contact-form');

$form->addInput('name', 'Imię i nazwisko')
     ->addInput('email', 'Adres email', ['type' => 'email'])
     ->addInput('phone', 'Telefon', ['type' => 'tel'])
     ->addTextarea('message', 'Wiadomość', ['rows' => 5])
     ->addSubmitButton('Wyślij wiadomość');

$form->validation([
    'name' => ['required', 'length' => ['min' => 2]],
    'email' => ['required', 'isEmail'],
    'message' => ['required', 'length' => ['min' => 10, 'max' => 1000]]
]);

if ($form->onSubmit()) {
    // Przetwarzanie formularza
    $data = $form->getData();
    echo "Dziękujemy za wiadomość!";
}

echo $form->render();
```

### Formularz z grupowaniem pól

```php
$form = new Form();
$form->setId('user-form');

$form->title('Dane osobowe')
     ->startGroup(6)
     ->addInput('first_name', 'Imię')
     ->addInput('last_name', 'Nazwisko')
     ->stopGroup()
     ->startGroup(4)
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addInput('phone', 'Telefon')
     ->stopGroup()
     ->addCheckbox('newsletter', 'Zapisz się do newslettera')
     ->addSubmitButton('Zapisz');

echo $form->render();
```

## Walidacja

Biblioteka oferuje bogaty zestaw walidatorów:

- `required` - pole wymagane
- `checked` - checkbox musi być zaznaczony
- `length` - walidacja długości tekstu
- `isEmail` - walidacja adresu email
- `isInteger` - walidacja liczby całkowitej
- `isDecimal` - walidacja liczby dziesiętnej
- `enum` - walidacja wartości enum

### Własne walidatory

```php
$form->validation([
    'password' => [
        'required',
        function($value) {
            if (strlen($value) < 8) {
                throw new ValidationException('Hasło musi mieć co najmniej 8 znaków');
            }
        }
    ]
]);
```

## Konfiguracja

### Zmiana języka komunikatów walidacji

```php
use NimblePHP\Form\Validation;

// Ustawienie języka polskiego
Validation::changeLanguage('PL');
```

### Wyłączenie węzła form

```php
$form->setAddFormNode(false); // Renderuje tylko pola bez tagu <form>
```

## Wymagania systemowe

- PHP >= 8.2
- NimblePHP Framework >= 0.2.0
- krzysztofzylka/file ^1.0.3
- krzysztofzylka/arrays ^1.0.2

## Licencja

MIT License - szczegóły w pliku [LICENSE](../LICENSE)

## Autor

Krzysztof Żyłka (krzysztofzylka@yahoo.com)