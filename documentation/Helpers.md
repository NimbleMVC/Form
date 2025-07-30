# Trait Helpers

Trait zawierający funkcje pomocnicze dla formularzy w bibliotece NimblePHP Form. Zapewnia narzędzia do generowania atrybutów HTML, nazw pól, identyfikatorów oraz zarządzania danymi.

## Namespace

```php
NimblePHP\Form\Traits\Helpers
```

## Metody chronione

### `generateAttributes(array $attributes): string`

Generuje atrybuty HTML z tablicy.

**Parametry:**
- `$attributes` - tablica atrybutów (klucz => wartość)

**Zwraca:** string - atrybuty HTML

**Przykład:**
```php
$attributes = [
    'id' => 'my-field',
    'class' => 'form-control',
    'required' => true,
    'placeholder' => 'Wpisz tekst...'
];

$html = $this->generateAttributes($attributes);
// Wynik: ' id="my-field" class="form-control" required="1" placeholder="Wpisz tekst..."'
```

**Uwagi:**
- Wartości `null` i tablice są pomijane
- Jeśli wartość zawiera cudzysłów, używa apostrofów
- Wartości boolean są konwertowane na '1'

### `generateName(string $name, string $prefix = ''): string`

Generuje nazwę pola HTML z obsługą zagnieżdżonych struktur.

**Parametry:**
- `$name` - nazwa pola (może zawierać `/` dla zagnieżdżenia)
- `$prefix` - prefiks nazwy

**Zwraca:** string - nazwa pola w formacie HTML

**Przykład:**
```php
// Proste pole
$name = $this->generateName('username');
// Wynik: "username"

// Zagnieżdżone pole
$name = $this->generateName('user/profile/name');
// Wynik: "user[profile][name]"

// Z prefiksem
$name = $this->generateName('name', 'form_');
// Wynik: "form_name"

// Pole zaczynające się od "/"
$name = $this->generateName('/global/setting');
// Wynik: "/global[setting]"
```

### `generateId(string $name): string`

Generuje identyfikator HTML z nazwy pola.

**Parametry:**
- `$name` - nazwa pola

**Zwraca:** string - identyfikator w formacie camelCase

**Przykład:**
```php
$id = $this->generateId('user_name');
// Wynik: "userName"

$id = $this->generateId('user/profile/first_name');
// Wynik: "userProfileFirstName"

$id = $this->generateId('contact_email');
// Wynik: "contactEmail"
```

### `getDataByKey(?string $name): mixed`

Pobiera dane z formularza na podstawie nazwy pola.

**Parametry:**
- `$name` - nazwa pola (może być zagnieżdżona z `/`)

**Zwraca:** mixed - wartość pola lub null

**Przykład:**
```php
// Dane formularza
$this->setData([
    'name' => 'Jan Kowalski',
    'user' => [
        'email' => 'jan@example.com',
        'profile' => [
            'age' => 30
        ]
    ]
]);

$name = $this->getDataByKey('name');
// Wynik: "Jan Kowalski"

$email = $this->getDataByKey('user/email');
// Wynik: "jan@example.com"

$age = $this->getDataByKey('user/profile/age');
// Wynik: 30

$missing = $this->getDataByKey('nonexistent');
// Wynik: null
```

### `prepareData(): bool`

Przygotowuje dane formularza z żądania HTTP.

**Zwraca:** bool - true jeśli dane zostały przygotowane

**Przykład:**
```php
// Dla metody POST
if ($this->method === MethodEnum::POST) {
    $success = $this->prepareData();
    // Ustawi $this->data = $_POST
}

// Dla metody GET
if ($this->method === MethodEnum::GET) {
    $success = $this->prepareData();
    // Ustawi $this->data = $_GET
}
```

## Przykłady użycia

### Generowanie atrybutów HTML

```php
use NimblePHP\Form\Traits\Helpers;

class MyForm
{
    use Helpers;
    
    public function createField(): string
    {
        $attributes = [
            'type' => 'email',
            'name' => 'user_email',
            'id' => 'userEmail',
            'class' => 'form-control',
            'required' => true,
            'placeholder' => 'Wpisz adres email',
            'maxlength' => 100,
            'autocomplete' => 'email'
        ];
        
        $html = '<input' . $this->generateAttributes($attributes) . '>';
        
        return $html;
        // <input type="email" name="user_email" id="userEmail" class="form-control" required="1" placeholder="Wpisz adres email" maxlength="100" autocomplete="email">
    }
}
```

### Obsługa zagnieżdżonych nazw pól

```php
class NestedForm
{
    use Helpers;
    
    public function generateNestedFields(): array
    {
        $fields = [
            'name',
            'user/email',
            'user/profile/first_name',
            'user/profile/last_name',
            'user/address/street',
            'user/address/city',
            'settings/notifications/email',
            'settings/notifications/sms'
        ];
        
        $generatedFields = [];
        
        foreach ($fields as $field) {
            $generatedFields[] = [
                'original' => $field,
                'name' => $this->generateName($field),
                'id' => $this->generateId($field)
            ];
        }
        
        return $generatedFields;
        /*
        [
            ['original' => 'name', 'name' => 'name', 'id' => 'name'],
            ['original' => 'user/email', 'name' => 'user[email]', 'id' => 'userEmail'],
            ['original' => 'user/profile/first_name', 'name' => 'user[profile][first_name]', 'id' => 'userProfileFirstName'],
            // ...
        ]
        */
    }
}
```

### Pobieranie zagnieżdżonych danych

```php
class DataForm
{
    use Helpers;
    
    private array $data = [
        'user' => [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'profile' => [
                'age' => 30,
                'city' => 'Warszawa',
                'preferences' => [
                    'newsletter' => true,
                    'notifications' => false
                ]
            ]
        ],
        'settings' => [
            'theme' => 'dark',
            'language' => 'pl'
        ]
    ];
    
    public function getData(): array
    {
        return $this->data;
    }
    
    public function demonstrateDataAccess(): array
    {
        return [
            'user_name' => $this->getDataByKey('user/name'),
            'user_email' => $this->getDataByKey('user/email'),
            'user_age' => $this->getDataByKey('user/profile/age'),
            'user_city' => $this->getDataByKey('user/profile/city'),
            'newsletter' => $this->getDataByKey('user/profile/preferences/newsletter'),
            'theme' => $this->getDataByKey('settings/theme'),
            'nonexistent' => $this->getDataByKey('nonexistent/field')
        ];
        /*
        [
            'user_name' => 'Jan Kowalski',
            'user_email' => 'jan@example.com',
            'user_age' => 30,
            'user_city' => 'Warszawa',
            'newsletter' => true,
            'theme' => 'dark',
            'nonexistent' => null
        ]
        */
    }
}
```

### Tworzenie dynamicznych formularzy

```php
class DynamicForm
{
    use Helpers;
    
    public function createDynamicField(string $fieldName, string $fieldType, array $options = []): string
    {
        $name = $this->generateName($fieldName);
        $id = $this->generateId($fieldName);
        
        $baseAttributes = [
            'name' => $name,
            'id' => $id,
            'class' => $options['class'] ?? 'form-control'
        ];
        
        $attributes = array_merge($baseAttributes, $options['attributes'] ?? []);
        
        switch ($fieldType) {
            case 'input':
                $attributes['type'] = $options['type'] ?? 'text';
                return '<input' . $this->generateAttributes($attributes) . '>';
                
            case 'textarea':
                $content = $attributes['value'] ?? '';
                unset($attributes['value']);
                return '<textarea' . $this->generateAttributes($attributes) . '>' . $content . '</textarea>';
                
            case 'select':
                $optionsHtml = '';
                foreach ($options['options'] ?? [] as $value => $label) {
                    $selected = ($options['selected'] ?? null) === $value ? ' selected' : '';
                    $optionsHtml .= "<option value=\"{$value}\"{$selected}>{$label}</option>";
                }
                return '<select' . $this->generateAttributes($attributes) . '>' . $optionsHtml . '</select>';
                
            default:
                return '';
        }
    }
    
    public function createFormSection(): string
    {
        $html = '';
        
        // Pole tekstowe
        $html .= $this->createDynamicField('user/name', 'input', [
            'type' => 'text',
            'attributes' => ['placeholder' => 'Imię i nazwisko', 'required' => true]
        ]);
        
        // Pole email
        $html .= $this->createDynamicField('user/email', 'input', [
            'type' => 'email',
            'attributes' => ['placeholder' => 'Email', 'required' => true]
        ]);
        
        // Textarea
        $html .= $this->createDynamicField('user/bio', 'textarea', [
            'attributes' => ['rows' => 4, 'placeholder' => 'O sobie...']
        ]);
        
        // Select
        $html .= $this->createDynamicField('user/country', 'select', [
            'options' => [
                'pl' => 'Polska',
                'de' => 'Niemcy',
                'fr' => 'Francja'
            ],
            'selected' => 'pl'
        ]);
        
        return $html;
    }
}
```

### Obsługa specjalnych przypadków atrybutów

```php
class AttributeForm
{
    use Helpers;
    
    public function demonstrateSpecialAttributes(): array
    {
        $examples = [];
        
        // Atrybuty z cudzysłowami
        $examples['quotes'] = $this->generateAttributes([
            'placeholder' => 'Wpisz "swoje" dane',
            'title' => "To jest 'specjalny' tooltip"
        ]);
        // Wynik: ' placeholder=\'Wpisz "swoje" dane\' title="To jest \'specjalny\' tooltip"'
        
        // Atrybuty boolean
        $examples['boolean'] = $this->generateAttributes([
            'required' => true,
            'disabled' => false,
            'readonly' => true,
            'multiple' => false
        ]);
        // Wynik: ' required="1" readonly="1"' (false są pomijane)
        
        // Atrybuty null i tablice (pomijane)
        $examples['null_array'] = $this->generateAttributes([
            'id' => 'field',
            'class' => null,
            'data-options' => ['a', 'b', 'c'],
            'name' => 'test'
        ]);
        // Wynik: ' id="field" name="test"'
        
        // Atrybuty numeryczne
        $examples['numeric'] = $this->generateAttributes([
            'min' => 0,
            'max' => 100,
            'step' => 0.01,
            'tabindex' => 1
        ]);
        // Wynik: ' min="0" max="100" step="0.01" tabindex="1"'
        
        return $examples;
    }
}
```

### Integracja z metodami HTTP

```php
use NimblePHP\Form\Enum\MethodEnum;

class HttpForm
{
    use Helpers;
    
    private MethodEnum $method;
    private array $data = [];
    
    public function __construct(MethodEnum $method = MethodEnum::POST)
    {
        $this->method = $method;
    }
    
    public function loadData(): bool
    {
        // Przygotowanie danych na podstawie metody HTTP
        $success = $this->prepareData();
        
        if ($success) {
            // Demonstracja dostępu do danych
            $examples = [
                'simple' => $this->getDataByKey('name'),
                'nested' => $this->getDataByKey('user/profile/age'),
                'missing' => $this->getDataByKey('nonexistent')
            ];
            
            return true;
        }
        
        return false;
    }
    
    public function setData(array $data): void
    {
        $this->data = $data;
    }
    
    public function getData(): array
    {
        return $this->data;
    }
}
```

## Uwagi implementacyjne

1. **Bezpieczeństwo**: Metody automatycznie escapują wartości atrybutów HTML
2. **Wydajność**: Funkcje są zoptymalizowane pod kątem częstego użycia
3. **Kompatybilność**: Obsługuje różne formaty nazw pól (snake_case, camelCase)
4. **Zagnieżdżenie**: Pełna obsługa zagnieżdżonych struktur danych
5. **Walidacja**: Automatyczne pomijanie nieprawidłowych wartości

## Zastosowania

Trait Helpers jest używany przez:
- Klasę `Form` - do generowania HTML
- Trait `Field` - do tworzenia pól formularza
- Trait `Validation` - do dostępu do danych walidacji

## Zobacz również

- [Form](Form.md) - Główna klasa formularza
- [Field](Field.md) - Trait do zarządzania polami
- [Validation](Validation.md) - Walidacja danych