# Klasa Validation

Klasa odpowiedzialna za walidację danych formularzy w bibliotece NimblePHP Form. Zapewnia zestaw predefiniowanych walidatorów oraz możliwość tworzenia własnych reguł walidacji.

## Namespace

```php
NimblePHP\Form\Validation
```

## Właściwości statyczne

### `$language` (public static array)
Tablica z komunikatami błędów walidacji w różnych językach.

**Domyślne komunikaty (angielski):**
```php
[
    'required' => 'This field cannot be empty.',
    'checked' => 'The checkbox must be checked.',
    'length_min' => 'The field cannot have fewer than {length} [character,characters,characters].',
    'length_max' => 'The field cannot have more than {length} [character,characters,characters].',
    'isEmail' => 'The provided email address is invalid.',
    'isInteger' => 'The provided value must be an integer.',
    'invalidInt' => 'Invalid numeric value.',
    'decimalMax' => 'The field may not have more than {decimal} [decimal place, decimal places].',
    'invalidEnum' => 'Incorrect value.'
]
```

## Właściwości instancji

### `$fields` (protected array)
Lista pól do walidacji z przypisanymi regułami.

### `$data` (protected array)
Dane POST lub GET do walidacji.

### `$validationErrors` (protected array)
Tablica z błędami walidacji.

## Konstruktor

### `__construct(array $validationList, array $data)`

Inicjalizuje walidator z regułami i danymi.

**Parametry:**
- `$validationList` - tablica z regułami walidacji
- `$data` - dane do walidacji

**Przykład:**
```php
use NimblePHP\Form\Validation;

$validation = new Validation([
    'name' => ['required', 'length' => ['min' => 2]],
    'email' => ['required', 'isEmail']
], $_POST);
```

## Metody statyczne

### `changeLanguage(string $lang): void`

Zmienia język komunikatów błędów walidacji.

**Parametry:**
- `$lang` - kod języka ('PL' dla polskiego)

**Rzuca:**
- `NimbleException` - gdy język nie jest obsługiwany

**Przykład:**
```php
use NimblePHP\Form\Validation;

// Ustawienie języka polskiego
Validation::changeLanguage('PL');
```

**Komunikaty w języku polskim:**
```php
[
    'required' => 'Pole nie może być puste.',
    'checked' => 'Pole musi zostać zaznaczone.',
    'length_min' => 'Pole nie może mieć mniej niż {length} [znak,znaki,znaków].',
    'length_max' => 'Pole nie może mieć więcej niż {length} [znak,znaki,znaków].',
    'isEmail' => 'Podany adres e-mail jest niepoprawny.',
    'isInteger' => 'Podana wartość musi być liczbą całkowitą.',
    'invalidInt' => 'Niepoprawna wartość liczbowa.',
    'decimalMax' => 'Pole nie może mieć więcej niż {decimal} [miejsce, miejsca, miejsc] po przecinku.',
    'invalidEnum' => 'Nieprawidłowa wartość pola.'
]
```

## Metody publiczne

### `run(): array`

Uruchamia walidację wszystkich pól.

**Zwraca:** array - tablica z błędami walidacji (klucz = nazwa pola, wartość = komunikat błędu)

**Przykład:**
```php
$validation = new Validation([
    'name' => ['required'],
    'email' => ['required', 'isEmail']
], [
    'name' => '',
    'email' => 'invalid-email'
]);

$errors = $validation->run();
// $errors = [
//     'name' => 'Pole nie może być puste.',
//     'email' => 'Podany adres e-mail jest niepoprawny.'
// ]
```

## Predefiniowane walidatory

### `required`
Sprawdza czy pole nie jest puste.

**Przykład:**
```php
'name' => ['required']
```

### `checked`
Sprawdza czy checkbox jest zaznaczony.

**Przykład:**
```php
'terms' => ['checked']
```

### `length`
Waliduje długość tekstu.

**Parametry:**
- `min` - minimalna długość
- `max` - maksymalna długość

**Przykład:**
```php
'password' => ['length' => ['min' => 8, 'max' => 50]]
'name' => ['length' => ['min' => 2]]
'description' => ['length' => ['max' => 1000]]
```

### `isEmail`
Sprawdza poprawność adresu email.

**Przykład:**
```php
'email' => ['isEmail']
```

### `isInteger`
Sprawdza czy wartość jest liczbą całkowitą.

**Przykład:**
```php
'age' => ['isInteger']
```

### `isDecimal`
Sprawdza czy wartość jest liczbą dziesiętną.

**Parametry:**
- `maxPlaces` - maksymalna liczba miejsc po przecinku (domyślnie 2)

**Przykład:**
```php
'price' => ['isDecimal']
'weight' => ['isDecimal' => ['maxPlaces' => 3]]
```

### `enum`
Sprawdza czy wartość należy do określonego enum.

**Parametry:**
- Klasa enum

**Przykład:**
```php
use App\Enums\UserStatus;

'status' => ['enum' => UserStatus::class]
```

## Własne walidatory

Możesz tworzyć własne walidatory używając funkcji anonimowych lub callable.

**Przykład:**
```php
$validation = new Validation([
    'password' => [
        'required',
        function($value) {
            if (strlen($value) < 8) {
                throw new ValidationException('Hasło musi mieć co najmniej 8 znaków');
            }
            if (!preg_match('/[A-Z]/', $value)) {
                throw new ValidationException('Hasło musi zawierać wielką literę');
            }
            if (!preg_match('/[0-9]/', $value)) {
                throw new ValidationException('Hasło musi zawierać cyfrę');
            }
        }
    ],
    'email' => [
        'required',
        'isEmail',
        function($value) {
            // Sprawdzenie czy email nie jest już zajęty
            $userModel = new UserModel();
            if ($userModel->findByEmail($value)) {
                throw new ValidationException('Ten adres email jest już zajęty');
            }
        }
    ]
], $_POST);
```

## Przykłady użycia

### Podstawowa walidacja formularza

```php
use NimblePHP\Form\Validation;

// Ustawienie języka polskiego
Validation::changeLanguage('PL');

$validationRules = [
    'first_name' => ['required', 'length' => ['min' => 2]],
    'last_name' => ['required', 'length' => ['min' => 2]],
    'email' => ['required', 'isEmail'],
    'age' => ['required', 'isInteger'],
    'terms' => ['checked']
];

$validation = new Validation($validationRules, $_POST);
$errors = $validation->run();

if (empty($errors)) {
    echo "Formularz jest poprawny!";
} else {
    foreach ($errors as $field => $error) {
        echo "Błąd w polu {$field}: {$error}\n";
    }
}
```

### Walidacja z zagnieżdżonymi polami

```php
// Dane formularza
$data = [
    'user' => [
        'name' => 'Jan',
        'email' => 'jan@example.com',
        'address' => [
            'street' => 'Główna 1',
            'city' => 'Warszawa'
        ]
    ]
];

// Reguły walidacji (używając ścieżek z '/')
$rules = [
    'user/name' => ['required'],
    'user/email' => ['required', 'isEmail'],
    'user/address/street' => ['required'],
    'user/address/city' => ['required']
];

$validation = new Validation($rules, $data);
$errors = $validation->run();
```

### Walidacja z enum

```php
enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case MODERATOR = 'moderator';
}

$rules = [
    'name' => ['required'],
    'role' => ['required', 'enum' => UserRole::class]
];

$data = [
    'name' => 'Jan Kowalski',
    'role' => 'admin' // Musi być jedną z wartości enum
];

$validation = new Validation($rules, $data);
$errors = $validation->run();
```

### Walidacja liczb dziesiętnych

```php
$rules = [
    'price' => ['required', 'isDecimal'],
    'weight' => ['required', 'isDecimal' => ['maxPlaces' => 3]],
    'percentage' => ['required', 'isDecimal' => ['maxPlaces' => 1]]
];

$data = [
    'price' => '19.99',    // OK
    'weight' => '1.234',   // OK (3 miejsca po przecinku)
    'percentage' => '15.5' // OK (1 miejsce po przecinku)
];

$validation = new Validation($rules, $data);
$errors = $validation->run();
```

### Kompleksowa walidacja rejestracji

```php
use NimblePHP\Form\Validation;
use NimblePHP\Form\Exceptions\ValidationException;

Validation::changeLanguage('PL');

$rules = [
    'first_name' => ['required', 'length' => ['min' => 2, 'max' => 50]],
    'last_name' => ['required', 'length' => ['min' => 2, 'max' => 50]],
    'email' => [
        'required',
        'isEmail',
        function($value) {
            // Sprawdzenie czy email nie jest już zajęty
            $userModel = new UserModel();
            if ($userModel->emailExists($value)) {
                throw new ValidationException('Ten adres email jest już zajęty');
            }
        }
    ],
    'password' => [
        'required',
        'length' => ['min' => 8],
        function($value) {
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $value)) {
                throw new ValidationException('Hasło musi zawierać małą literę, wielką literę i cyfrę');
            }
        }
    ],
    'password_confirm' => [
        'required',
        function($value) use ($_POST) {
            if ($value !== $_POST['password']) {
                throw new ValidationException('Hasła nie są identyczne');
            }
        }
    ],
    'age' => ['required', 'isInteger'],
    'terms' => ['checked'],
    'newsletter' => [] // Opcjonalne pole
];

$validation = new Validation($rules, $_POST);
$errors = $validation->run();

if (empty($errors)) {
    // Rejestracja użytkownika
    echo "Użytkownik został zarejestrowany!";
} else {
    // Wyświetlenie błędów
    foreach ($errors as $field => $error) {
        echo "<div class='error'>{$error}</div>";
    }
}
```

### Walidacja w klasie Form

```php
use NimblePHP\Form\Form;

$form = new Form();
$form->addInput('name', 'Imię')
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addTextarea('message', 'Wiadomość')
     ->addSubmitButton('Wyślij');

// Walidacja
$form->validation([
    'name' => ['required', 'length' => ['min' => 2]],
    'email' => ['required', 'isEmail'],
    'message' => [
        'required',
        'length' => ['min' => 10, 'max' => 1000],
        function($value) {
            if (str_word_count($value) < 3) {
                throw new ValidationException('Wiadomość musi zawierać co najmniej 3 słowa');
            }
        }
    ]
]);

if ($form->onSubmit()) {
    $data = $form->getData();
    // Przetwarzanie danych...
} else {
    echo $form->render(); // Wyświetli błędy przy polach
}
```

## Uwagi

1. **Kolejność walidatorów**: Walidatory są wykonywane w kolejności podania. Pierwszy błąd przerywa walidację danego pola.

2. **Obsługa zagnieżdżonych danych**: Używaj notacji ze slashem (`/`) dla zagnieżdżonych pól.

3. **Infleksja**: Komunikaty w języku polskim automatycznie dostosowują formy gramatyczne liczb.

4. **Własne walidatory**: Muszą rzucać `ValidationException` w przypadku błędu.

5. **Wydajność**: Walidacja zatrzymuje się przy pierwszym błędzie dla każdego pola.

## Zobacz również

- [Form](Form.md) - Główna klasa formularza
- [ValidationException](ValidationException.md) - Wyjątek walidacji
- [ValidationTrait](ValidationTrait.md) - Trait do walidacji w formularzu