# Klasa Form

Główna klasa biblioteki NimblePHP Form odpowiedzialna za tworzenie, renderowanie i obsługę formularzy HTML.

## Namespace

```php
NimblePHP\Form\Form
```

## Używane traity

- `NimblePHP\Form\Traits\Helpers` - funkcje pomocnicze
- `NimblePHP\Form\Traits\Field` - zarządzanie polami formularza
- `NimblePHP\Form\Traits\Validation` - walidacja formularza

## Właściwości

### `$action` (protected string)
Akcja formularza (URL docelowy).

### `$method` (protected MethodEnum)
Metoda HTTP formularza (POST lub GET).

### `$request` (protected Request)
Instancja klasy Request z frameworka NimblePHP.

### `$addFormNode` (protected bool)
Określa czy dodawać tag `<form>` podczas renderowania. Domyślnie `true`.

### `$id` (protected ?string)
Identyfikator formularza. Domyślnie `null`.

### `$data` (protected array)
Dane wejściowe formularza.

## Konstruktor

### `__construct(?string $action = null, MethodEnum $method = MethodEnum::POST)`

Inicjalizuje nowy formularz.

**Parametry:**
- `$action` - URL docelowy formularza. Jeśli null, używa bieżącego URI
- `$method` - Metoda HTTP (domyślnie POST)

**Przykład:**
```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Enum\MethodEnum;

// Formularz z domyślnymi ustawieniami
$form = new Form();

// Formularz z niestandardową akcją i metodą GET
$form = new Form('/search', MethodEnum::GET);
```

## Metody publiczne

### `getId(): ?string`

Zwraca identyfikator formularza.

**Zwraca:** string|null - identyfikator formularza lub null

**Przykład:**
```php
$form = new Form();
$form->setId('my-form');
echo $form->getId(); // "my-form"
```

### `setId(?string $id): void`

Ustawia identyfikator formularza.

**Parametry:**
- `$id` - identyfikator formularza

**Przykład:**
```php
$form = new Form();
$form->setId('contact-form');
```

### `setAddFormNode(bool $addFormNode): self`

Określa czy dodawać tag `<form>` podczas renderowania.

**Parametry:**
- `$addFormNode` - true aby dodać tag form, false aby renderować tylko pola

**Zwraca:** self - dla fluent interface

**Przykład:**
```php
$form = new Form();
$form->setAddFormNode(false); // Renderuje tylko pola bez tagu <form>
```

### `getData(): array`

Zwraca dane formularza.

**Zwraca:** array - tablica z danymi formularza

**Przykład:**
```php
$form = new Form();
if ($form->onSubmit()) {
    $data = $form->getData();
    echo $data['name']; // wartość pola 'name'
}
```

### `setData(array $data): void`

Ustawia dane formularza.

**Parametry:**
- `$data` - tablica z danymi do ustawienia

**Przykład:**
```php
$form = new Form();
$form->setData([
    'name' => 'Jan Kowalski',
    'email' => 'jan@example.com'
]);
```

### `onSubmit(): bool`

Sprawdza czy formularz został poprawnie wysłany i przeszedł walidację.

**Zwraca:** bool - true jeśli formularz został wysłany i jest poprawny

**Przykład:**
```php
$form = new Form();
$form->setId('contact-form');

// Dodanie pól i walidacji...

if ($form->onSubmit()) {
    // Formularz został poprawnie wysłany
    $data = $form->getData();
    // Przetwarzanie danych...
} else {
    // Formularz nie został wysłany lub zawiera błędy
    echo $form->render();
}
```

### `render(): string`

Renderuje formularz do HTML.

**Zwraca:** string - kod HTML formularza

**Przykład:**
```php
$form = new Form();
$form->setId('my-form');
$form->addInput('name', 'Imię')
     ->addSubmitButton('Wyślij');

echo $form->render();
```

**Wygenerowany HTML:**
```html
<form action="" method="POST" id="my-form" class="ajax-form">
    <input type="hidden" name="formId" value="my-form">
    <div class="mb-3">
        <label for="name" class="form-label">Imię</label><br>
        <input name="name" id="name" type="input" class="form-control">
    </div>
    <div class="mb-3">
        <input type="submit" class="form-control btn btn-primary" value="Wyślij">
    </div>
</form>
<script>$("#my-form").ajaxform();</script>
```

## Przykłady użycia

### Prosty formularz kontaktowy

```php
use NimblePHP\Form\Form;

$form = new Form('/contact/send');
$form->setId('contact-form');

// Dodawanie pól
$form->addInput('name', 'Imię i nazwisko')
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addTextarea('message', 'Wiadomość')
     ->addSubmitButton('Wyślij');

// Walidacja
$form->validation([
    'name' => ['required'],
    'email' => ['required', 'isEmail'],
    'message' => ['required', 'length' => ['min' => 10]]
]);

// Obsługa wysłania
if ($form->onSubmit()) {
    $data = $form->getData();
    // Wysłanie emaila, zapis do bazy danych itp.
    echo "Dziękujemy za wiadomość!";
} else {
    echo $form->render();
}
```

### Formularz wyszukiwania (GET)

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Enum\MethodEnum;

$form = new Form('/search', MethodEnum::GET);
$form->setId('search-form');

$form->addInput('q', 'Szukaj', ['placeholder' => 'Wpisz słowa kluczowe...'])
     ->addSelect('category', [
         '' => 'Wszystkie kategorie',
         'news' => 'Aktualności',
         'products' => 'Produkty'
     ], null, 'Kategoria')
     ->addSubmitButton('Szukaj');

if ($form->onSubmit()) {
    $data = $form->getData();
    // Wykonanie wyszukiwania...
}

echo $form->render();
```

### Formularz z grupowaniem pól

```php
$form = new Form();
$form->setId('user-registration');

$form->title('Rejestracja użytkownika')
     ->startGroup(6) // Kolumny Bootstrap
     ->addInput('first_name', 'Imię')
     ->addInput('last_name', 'Nazwisko')
     ->stopGroup()
     ->startGroup(4)
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addInput('phone', 'Telefon')
     ->stopGroup()
     ->addCheckbox('terms', 'Akceptuję regulamin')
     ->addSubmitButton('Zarejestruj się');

$form->validation([
    'first_name' => ['required'],
    'last_name' => ['required'],
    'email' => ['required', 'isEmail'],
    'terms' => ['checked']
]);

if ($form->onSubmit()) {
    // Rejestracja użytkownika...
}

echo $form->render();
```

### Formularz bez tagu form (do osadzania)

```php
$form = new Form();
$form->setAddFormNode(false); // Wyłączenie tagu <form>

$form->addInput('username', 'Nazwa użytkownika')
     ->addInput('password', 'Hasło', ['type' => 'password'])
     ->addSubmitButton('Zaloguj');

// Wygeneruje tylko pola bez tagu <form>
echo '<form method="POST" action="/login" class="custom-form">';
echo $form->render();
echo '</form>';
```

## Uwagi

1. **AJAX Support**: Formularz automatycznie dodaje klasę `ajax-form` i skrypt jQuery dla obsługi AJAX
2. **Bootstrap**: Generowany HTML używa klas Bootstrap 5
3. **Bezpieczeństwo**: Automatyczne dodawanie pola `formId` dla weryfikacji formularza
4. **Walidacja**: Błędy walidacji są automatycznie wyświetlane przy polach
5. **Fluent Interface**: Większość metod zwraca `$this` dla łańcuchowego wywoływania

## Zobacz również

- [FormBuilder](FormBuilder.md) - Abstrakcyjna klasa do budowania formularzy
- [Field](Field.md) - Trait z metodami do dodawania pól
- [Validation](Validation.md) - Klasa walidacji
- [MethodEnum](MethodEnum.md) - Enum metod HTTP