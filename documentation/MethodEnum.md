# Enum MethodEnum

Enum definiujący dostępne metody HTTP dla formularzy w bibliotece NimblePHP Form.

## Namespace

```php
NimblePHP\Form\Enum\MethodEnum
```

## Typ bazowy

```php
enum MethodEnum: string
```

## Wartości

### `POST`
Metoda HTTP POST - używana do wysyłania danych formularza, które mogą modyfikować stan aplikacji.

**Wartość:** `'POST'`

**Przykład użycia:**
```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Enum\MethodEnum;

$form = new Form('/submit', MethodEnum::POST);
```

### `GET`
Metoda HTTP GET - używana do wysyłania danych formularza w URL, typowo dla wyszukiwania i filtrowania.

**Wartość:** `'GET'`

**Przykład użycia:**
```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Enum\MethodEnum;

$form = new Form('/search', MethodEnum::GET);
```

## Przykłady użycia

### Formularz POST (domyślny)

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Enum\MethodEnum;

// Explicit POST
$form = new Form('/contact/send', MethodEnum::POST);

// Lub domyślnie (POST jest wartością domyślną)
$form = new Form('/contact/send');

$form->addInput('name', 'Imię')
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addTextarea('message', 'Wiadomość')
     ->addSubmitButton('Wyślij');

if ($form->onSubmit()) {
    $data = $form->getData(); // Dane z $_POST
    // Przetwarzanie danych...
}

echo $form->render();
```

**Wygenerowany HTML:**
```html
<form action="/contact/send" method="POST" class="ajax-form">
    <!-- pola formularza -->
</form>
```

### Formularz GET (wyszukiwanie)

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Enum\MethodEnum;

$form = new Form('/search', MethodEnum::GET);
$form->setId('search-form');

$form->addInput('q', 'Szukaj', ['placeholder' => 'Wpisz słowa kluczowe...'])
     ->addSelect('category', [
         '' => 'Wszystkie kategorie',
         'news' => 'Aktualności',
         'products' => 'Produkty',
         'articles' => 'Artykuły'
     ], null, 'Kategoria')
     ->addSelect('sort', [
         'date_desc' => 'Najnowsze',
         'date_asc' => 'Najstarsze',
         'name_asc' => 'Alfabetycznie A-Z',
         'name_desc' => 'Alfabetycznie Z-A'
     ], 'date_desc', 'Sortowanie')
     ->addSubmitButton('Szukaj');

if ($form->onSubmit()) {
    $data = $form->getData(); // Dane z $_GET
    
    // Wykonanie wyszukiwania
    $searchResults = performSearch($data['q'], $data['category'], $data['sort']);
    
    // Wyświetlenie wyników
    displayResults($searchResults);
}

echo $form->render();
```

**Wygenerowany HTML:**
```html
<form action="/search" method="GET" id="search-form" class="ajax-form">
    <!-- pola formularza -->
</form>
```

**Przykładowy URL po wysłaniu:**
```
/search?q=nimble&category=news&sort=date_desc
```

### Użycie w FormBuilder

```php
<?php

namespace App\Form;

use NimblePHP\Form\FormBuilder;
use NimblePHP\Form\Enum\MethodEnum;

class SearchFormBuilder extends FormBuilder
{
    protected MethodEnum $method = MethodEnum::GET;
    protected ?string $action = '/search';

    public function create(): void
    {
        $this->form->addInput('q', 'Szukaj')
                   ->addSelect('category', $this->getCategories(), null, 'Kategoria')
                   ->addSubmitButton('Szukaj');
    }

    public function validation(): array
    {
        return [
            'q' => ['required', 'length' => ['min' => 3]]
        ];
    }

    public function onSubmit(): void
    {
        $data = $this->form->getData();
        
        // Przekierowanie z wynikami wyszukiwania
        $this->controller->redirect('/search/results?' . http_build_query($data));
    }

    private function getCategories(): array
    {
        return [
            '' => 'Wszystkie',
            'news' => 'Aktualności',
            'products' => 'Produkty'
        ];
    }
}
```

### Formularz filtrowania

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Enum\MethodEnum;

$form = new Form('/products', MethodEnum::GET);
$form->setId('filter-form');

$form->addSelect('brand', [
         '' => 'Wszystkie marki',
         'apple' => 'Apple',
         'samsung' => 'Samsung',
         'xiaomi' => 'Xiaomi'
     ], $_GET['brand'] ?? '', 'Marka')
     ->addFloatInput('price_min', 'Cena od')
     ->addFloatInput('price_max', 'Cena do')
     ->addSelect('availability', [
         '' => 'Wszystkie',
         'in_stock' => 'Dostępne',
         'out_of_stock' => 'Niedostępne'
     ], $_GET['availability'] ?? '', 'Dostępność')
     ->addSubmitButton('Filtruj');

// Ustawienie danych z URL
$form->setData($_GET);

if ($form->onSubmit()) {
    $filters = $form->getData();
    
    // Filtrowanie produktów
    $products = filterProducts($filters);
    
    // Wyświetlenie produktów
    displayProducts($products);
}

echo $form->render();
```

### Różnice między POST a GET

| Aspekt | POST | GET |
|--------|------|-----|
| **Dane** | W ciele żądania | W URL (query string) |
| **Bezpieczeństwo** | Dane niewidoczne w URL | Dane widoczne w URL |
| **Rozmiar danych** | Brak limitu | Ograniczony przez długość URL |
| **Zakładki** | Nie można dodać do zakładek | Można dodać do zakładek |
| **Historia przeglądarki** | Nie zapisuje danych | Zapisuje pełny URL z danymi |
| **Odświeżenie strony** | Przeglądarka pyta o ponowne wysłanie | Automatyczne ponowne wysłanie |
| **SEO** | Nie indeksowane | Może być indeksowane |
| **Użycie** | Formularze modyfikujące dane | Wyszukiwanie, filtrowanie |

### Kiedy używać POST

```php
// ✅ Rejestracja użytkownika
$form = new Form('/auth/register', MethodEnum::POST);

// ✅ Kontakt
$form = new Form('/contact/send', MethodEnum::POST);

// ✅ Dodawanie produktu
$form = new Form('/admin/products/add', MethodEnum::POST);

// ✅ Zmiana hasła
$form = new Form('/user/change-password', MethodEnum::POST);

// ✅ Upload pliku
$form = new Form('/upload', MethodEnum::POST);
```

### Kiedy używać GET

```php
// ✅ Wyszukiwanie
$form = new Form('/search', MethodEnum::GET);

// ✅ Filtrowanie produktów
$form = new Form('/products', MethodEnum::GET);

// ✅ Paginacja
$form = new Form('/articles', MethodEnum::GET);

// ✅ Sortowanie listy
$form = new Form('/users', MethodEnum::GET);

// ✅ Formularz raportów
$form = new Form('/reports', MethodEnum::GET);
```

## Uwagi

1. **Domyślna wartość**: Jeśli nie określisz metody, formularz użyje POST.

2. **Bezpieczeństwo**: Nigdy nie używaj GET dla danych wrażliwych (hasła, dane osobowe).

3. **Limity**: Metoda GET ma ograniczenia długości URL (zwykle 2048 znaków).

4. **SEO**: Formularze GET mogą być indeksowane przez wyszukiwarki.

5. **Zakładki**: Tylko formularze GET można dodać do zakładek przeglądarki.

## Zobacz również

- [Form](Form.md) - Główna klasa formularza
- [FormBuilder](FormBuilder.md) - Builder formularzy
- [Validation](Validation.md) - Walidacja danych