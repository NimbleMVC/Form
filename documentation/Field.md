# Trait Field

Trait zawierający metody do zarządzania polami formularza w bibliotece NimblePHP Form. Zapewnia funkcjonalność dodawania różnych typów pól oraz ich renderowania.

## Namespace

```php
NimblePHP\Form\Traits\Field
```

## Używane traity

- `NimblePHP\Form\Traits\Helpers` - funkcje pomocnicze

## Właściwości

### `$fields` (private array)
Tablica przechowująca wszystkie pola formularza.

### `$colAttributes` (protected array)
Atrybuty kolumn dla grupowania pól (Bootstrap).

## Metody publiczne

### `addField(string $type, ?string $name, ?string $title, array $attributes = [], array $options = [], ?string $class = null): self`

Uniwersalna metoda do dodawania pól formularza.

**Parametry:**
- `$type` - typ pola (input, textarea, select, checkbox, itp.)
- `$name` - nazwa pola
- `$title` - etykieta pola
- `$attributes` - atrybuty HTML pola
- `$options` - opcje specyficzne dla typu pola
- `$class` - klasa CSS pola

**Zwraca:** self - dla fluent interface

**Przykład:**
```php
$form->addField('input', 'username', 'Nazwa użytkownika', [
    'type' => 'text',
    'placeholder' => 'Wpisz nazwę użytkownika',
    'required' => true
], [], 'form-control');
```

### `addInput(string $name, ?string $title = null, array $attributes = []): self`

Dodaje pole tekstowe.

**Parametry:**
- `$name` - nazwa pola
- `$title` - etykieta pola
- `$attributes` - atrybuty HTML

**Zwraca:** self

**Przykład:**
```php
$form->addInput('name', 'Imię i nazwisko')
     ->addInput('email', 'Email', ['type' => 'email', 'required' => true])
     ->addInput('phone', 'Telefon', ['type' => 'tel', 'pattern' => '[0-9]{9}']);
```

### `addCheckbox(string $name, ?string $title = null, array $attributes = []): self`

Dodaje pole checkbox.

**Parametry:**
- `$name` - nazwa pola
- `$title` - etykieta pola
- `$attributes` - atrybuty HTML

**Zwraca:** self

**Przykład:**
```php
$form->addCheckbox('newsletter', 'Zapisz się do newslettera')
     ->addCheckbox('terms', 'Akceptuję regulamin', ['required' => true]);
```

**Uwaga:** Checkbox automatycznie generuje ukryte pole z wartością 0/1.

### `addFloatInput(string $name, ?string $title = null, array $attributes = []): self`

Dodaje pole dla liczb dziesiętnych.

**Parametry:**
- `$name` - nazwa pola
- `$title` - etykieta pola
- `$attributes` - atrybuty HTML

**Zwraca:** self

**Przykład:**
```php
$form->addFloatInput('price', 'Cena', ['min' => 0, 'max' => 9999.99])
     ->addFloatInput('weight', 'Waga (kg)', ['step' => 0.1]);
```

**Uwaga:** Automatycznie ustawia `step="0.01"` i `type="number"`.

### `addTextarea(string $name, ?string $title = null, array $attributes = []): self`

Dodaje pole tekstowe wieloliniowe.

**Parametry:**
- `$name` - nazwa pola
- `$title` - etykieta pola
- `$attributes` - atrybuty HTML

**Zwraca:** self

**Przykład:**
```php
$form->addTextarea('message', 'Wiadomość', [
    'rows' => 5,
    'cols' => 50,
    'placeholder' => 'Wpisz swoją wiadomość...'
])
->addTextarea('description', 'Opis', ['rows' => 10, 'maxlength' => 1000]);
```

### `addSelect(string $name, array $options, null|string|array $selectedKey = null, ?string $title = null, array $attributes = []): self`

Dodaje listę rozwijaną.

**Parametry:**
- `$name` - nazwa pola
- `$options` - opcje do wyboru (klucz => wartość)
- `$selectedKey` - domyślnie wybrana opcja
- `$title` - etykieta pola
- `$attributes` - atrybuty HTML

**Zwraca:** self

**Przykład:**
```php
// Prosty select
$form->addSelect('country', [
    'pl' => 'Polska',
    'de' => 'Niemcy',
    'fr' => 'Francja'
], 'pl', 'Kraj');

// Select wielokrotny
$form->addSelect('categories', [
    '1' => 'Technologia',
    '2' => 'Sport',
    '3' => 'Kultura'
], ['1', '3'], 'Kategorie', ['multiple' => true]);

// Select z niestandardowymi stylami
$form->addSelect('status', [
    'active' => 'Aktywny',
    'inactive' => 'Nieaktywny',
    'pending' => 'Oczekujący'
], null, 'Status', [
    'optionsClass' => [
        'active' => 'text-success',
        'inactive' => 'text-danger',
        'pending' => 'text-warning'
    ]
]);
```

### `addInputHidden(string $name, string $value): self`

Dodaje ukryte pole formularza.

**Parametry:**
- `$name` - nazwa pola
- `$value` - wartość pola

**Zwraca:** self

**Przykład:**
```php
$form->addInputHidden('user_id', '123')
     ->addInputHidden('token', $csrfToken)
     ->addInputHidden('action', 'update');
```

### `addSubmitButton(string $value, ?array $attributes = []): self`

Dodaje przycisk wysyłania formularza.

**Parametry:**
- `$value` - tekst na przycisku
- `$attributes` - atrybuty HTML

**Zwraca:** self

**Przykład:**
```php
$form->addSubmitButton('Wyślij')
     ->addSubmitButton('Zapisz zmiany', ['class' => 'btn btn-success'])
     ->addSubmitButton('Usuń', ['class' => 'btn btn-danger', 'onclick' => 'return confirm("Czy na pewno?")']);
```

### `addRawCustomData(string $content): self`

Dodaje niestandardową zawartość HTML do formularza.

**Parametry:**
- `$content` - kod HTML do wstawienia

**Zwraca:** self

**Przykład:**
```php
$form->addInput('name', 'Imię')
     ->addRawCustomData('<div class="alert alert-info">Uwaga: Wszystkie pola są wymagane</div>')
     ->addInput('email', 'Email')
     ->addRawCustomData('<hr>')
     ->addSubmitButton('Wyślij');
```

### `startGroup(int $col = 6, array $rowAttributes = [], array $colAttributes = []): self`

Rozpoczyna grupę pól z układem kolumnowym (Bootstrap).

**Parametry:**
- `$col` - szerokość kolumny (1-12)
- `$rowAttributes` - atrybuty dla wiersza
- `$colAttributes` - atrybuty dla kolumny

**Zwraca:** self

**Przykład:**
```php
$form->startGroup(6) // Kolumna o szerokości 6
     ->addInput('first_name', 'Imię')
     ->addInput('last_name', 'Nazwisko')
     ->stopGroup()
     ->startGroup(4, ['class' => 'mt-3'], ['class' => 'border p-2'])
     ->addInput('email', 'Email')
     ->addInput('phone', 'Telefon')
     ->stopGroup();
```

### `stopGroup(): self`

Kończy grupę pól.

**Zwraca:** self

**Przykład:** Zobacz `startGroup()`

### `title(string $title): self`

Dodaje tytuł sekcji formularza.

**Parametry:**
- `$title` - tekst tytułu

**Zwraca:** self

**Przykład:**
```php
$form->title('Dane osobowe')
     ->addInput('first_name', 'Imię')
     ->addInput('last_name', 'Nazwisko')
     ->title('Dane kontaktowe')
     ->addInput('email', 'Email')
     ->addInput('phone', 'Telefon');
```

## Przykłady użycia

### Prosty formularz kontaktowy

```php
use NimblePHP\Form\Form;

$form = new Form('/contact');
$form->setId('contact-form');

$form->title('Formularz kontaktowy')
     ->addInput('name', 'Imię i nazwisko', ['required' => true])
     ->addInput('email', 'Email', ['type' => 'email', 'required' => true])
     ->addInput('phone', 'Telefon', ['type' => 'tel'])
     ->addSelect('subject', [
         'general' => 'Zapytanie ogólne',
         'support' => 'Wsparcie techniczne',
         'sales' => 'Sprzedaż',
         'other' => 'Inne'
     ], null, 'Temat')
     ->addTextarea('message', 'Wiadomość', [
         'rows' => 5,
         'required' => true,
         'placeholder' => 'Opisz swoje zapytanie...'
     ])
     ->addCheckbox('newsletter', 'Chcę otrzymywać newsletter')
     ->addSubmitButton('Wyślij wiadomość');

echo $form->render();
```

### Formularz rejestracji z grupowaniem

```php
$form = new Form('/register');
$form->setId('registration-form');

$form->title('Rejestracja użytkownika')
     
     // Grupa: Dane osobowe
     ->addRawCustomData('<h4>Dane osobowe</h4>')
     ->startGroup(6)
     ->addInput('first_name', 'Imię', ['required' => true])
     ->addInput('last_name', 'Nazwisko', ['required' => true])
     ->stopGroup()
     
     ->startGroup(4)
     ->addInput('birth_date', 'Data urodzenia', ['type' => 'date'])
     ->addSelect('gender', [
         '' => 'Wybierz płeć',
         'male' => 'Mężczyzna',
         'female' => 'Kobieta',
         'other' => 'Inna'
     ], null, 'Płeć')
     ->stopGroup()
     
     // Grupa: Dane kontaktowe
     ->addRawCustomData('<h4>Dane kontaktowe</h4>')
     ->startGroup(6)
     ->addInput('email', 'Email', ['type' => 'email', 'required' => true])
     ->addInput('phone', 'Telefon', ['type' => 'tel'])
     ->stopGroup()
     
     // Grupa: Hasło
     ->addRawCustomData('<h4>Hasło</h4>')
     ->startGroup(6)
     ->addInput('password', 'Hasło', ['type' => 'password', 'required' => true])
     ->addInput('password_confirm', 'Potwierdź hasło', ['type' => 'password', 'required' => true])
     ->stopGroup()
     
     // Zgody
     ->addRawCustomData('<h4>Zgody</h4>')
     ->addCheckbox('terms', 'Akceptuję regulamin', ['required' => true])
     ->addCheckbox('privacy', 'Akceptuję politykę prywatności', ['required' => true])
     ->addCheckbox('newsletter', 'Chcę otrzymywać newsletter')
     
     ->addSubmitButton('Zarejestruj się', ['class' => 'btn btn-primary btn-lg']);

echo $form->render();
```

### Formularz produktu z różnymi typami pól

```php
$form = new Form('/admin/products');
$form->setId('product-form');

$form->title('Dodaj produkt')
     
     // Podstawowe informacje
     ->addInput('name', 'Nazwa produktu', ['required' => true, 'maxlength' => 100])
     ->addTextarea('description', 'Opis', [
         'rows' => 4,
         'placeholder' => 'Opisz produkt...'
     ])
     
     // Cena i dostępność
     ->startGroup(4)
     ->addFloatInput('price', 'Cena (PLN)', ['min' => 0, 'required' => true])
     ->addInput('quantity', 'Ilość', ['type' => 'number', 'min' => 0])
     ->stopGroup()
     
     ->startGroup(4)
     ->addSelect('category_id', [
         '1' => 'Elektronika',
         '2' => 'Odzież',
         '3' => 'Dom i ogród',
         '4' => 'Sport'
     ], null, 'Kategoria', ['required' => true])
     ->addSelect('status', [
         'active' => 'Aktywny',
         'inactive' => 'Nieaktywny',
         'draft' => 'Szkic'
     ], 'draft', 'Status')
     ->stopGroup()
     
     // Opcje
     ->addCheckbox('featured', 'Produkt polecany')
     ->addCheckbox('on_sale', 'Promocja')
     ->addCheckbox('free_shipping', 'Darmowa dostawa')
     
     // Ukryte pola
     ->addInputHidden('user_id', '1')
     ->addInputHidden('created_at', date('Y-m-d H:i:s'))
     
     ->addSubmitButton('Zapisz produkt', ['class' => 'btn btn-success']);

echo $form->render();
```

### Formularz wyszukiwania zaawansowanego

```php
use NimblePHP\Form\Enum\MethodEnum;

$form = new Form('/search', MethodEnum::GET);
$form->setId('advanced-search');

$form->title('Wyszukiwanie zaawansowane')
     
     ->addInput('q', 'Szukaj', [
         'placeholder' => 'Wpisz słowa kluczowe...',
         'class' => 'form-control form-control-lg'
     ])
     
     ->startGroup(4)
     ->addSelect('category', [
         '' => 'Wszystkie kategorie',
         'news' => 'Aktualności',
         'articles' => 'Artykuły',
         'products' => 'Produkty',
         'events' => 'Wydarzenia'
     ], $_GET['category'] ?? '', 'Kategoria')
     
     ->addSelect('sort', [
         'relevance' => 'Trafność',
         'date_desc' => 'Najnowsze',
         'date_asc' => 'Najstarsze',
         'title_asc' => 'Alfabetycznie A-Z',
         'title_desc' => 'Alfabetycznie Z-A'
     ], $_GET['sort'] ?? 'relevance', 'Sortowanie')
     ->stopGroup()
     
     ->startGroup(4)
     ->addInput('date_from', 'Data od', ['type' => 'date'])
     ->addInput('date_to', 'Data do', ['type' => 'date'])
     ->stopGroup()
     
     ->addSelect('tags', [
         'php' => 'PHP',
         'javascript' => 'JavaScript',
         'python' => 'Python',
         'react' => 'React',
         'vue' => 'Vue.js'
     ], $_GET['tags'] ?? [], 'Tagi', ['multiple' => true])
     
     ->addSubmitButton('Szukaj', ['class' => 'btn btn-primary'])
     ->addRawCustomData('<a href="/search" class="btn btn-secondary ms-2">Wyczyść</a>');

// Ustawienie danych z URL
$form->setData($_GET);

echo $form->render();
```

## Renderowanie pól

Każde pole jest automatycznie renderowane z:

1. **Kontenerem**: `<div class="mb-3">` (lub bez dla ukrytych pól)
2. **Etykietą**: `<label>` (jeśli podana)
3. **Polem**: odpowiedni tag HTML z atrybutami
4. **Błędami walidacji**: `<div class="validation text-danger">` (jeśli występują)

### Przykład wygenerowanego HTML

```php
$form->addInput('email', 'Email', ['type' => 'email', 'required' => true]);
```

**Wygeneruje:**
```html
<div class="mb-3">
    <label for="email" class="form-label">Email</label><br>
    <input name="email" id="email" type="email" class="form-control" required>
</div>
```

## Uwagi

1. **Bootstrap**: Wszystkie pola używają klas Bootstrap 5
2. **Fluent Interface**: Wszystkie metody zwracają `$this`
3. **Automatyczne ID**: ID pól są generowane automatycznie z nazwy
4. **Walidacja**: Błędy walidacji są automatycznie wyświetlane
5. **Dane domyślne**: Pola automatycznie wypełniają się danymi z `setData()`

## Zobacz również

- [Form](Form.md) - Główna klasa formularza
- [Helpers](Helpers.md) - Trait z funkcjami pomocniczymi
- [Validation](Validation.md) - Walidacja pól