# Trait Validation

Trait zawierający funkcjonalność walidacji formularzy w bibliotece NimblePHP Form. Zapewnia integrację z klasą `Validation` oraz zarządzanie błędami walidacji.

## Namespace

```php
NimblePHP\Form\Traits\Validation
```

## Właściwości statyczne

### `$VALIDATIONS` (public static array)
Globalna tablica z błędami walidacji wszystkich formularzy.

## Właściwości instancji

### `$validationErrors` (protected array)
Tablica z błędami walidacji dla bieżącego formularza.

## Metody publiczne

### `validation(array $validations = []): bool`

Uruchamia walidację formularza z podanymi regułami.

**Parametry:**
- `$validations` - tablica z regułami walidacji

**Zwraca:** bool - true jeśli walidacja przebiegła pomyślnie (może zawierać błędy)

**Przykład:**
```php
use NimblePHP\Form\Form;

$form = new Form();
$form->addInput('name', 'Imię')
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addSubmitButton('Wyślij');

// Walidacja
$form->validation([
    'name' => ['required', 'length' => ['min' => 2]],
    'email' => ['required', 'isEmail']
]);

if ($form->onSubmit()) {
    // Formularz został poprawnie wysłany i zwalidowany
    $data = $form->getData();
} else {
    // Formularz zawiera błędy lub nie został wysłany
    echo $form->render(); // Wyświetli błędy
}
```

### `addValidation(string $fieldName, string $validationText): void`

Dodaje błąd walidacji do określonego pola.

**Parametry:**
- `$fieldName` - nazwa pola
- `$validationText` - tekst błędu walidacji

**Przykład:**
```php
$form = new Form();
$form->addInput('username', 'Nazwa użytkownika')
     ->addSubmitButton('Sprawdź');

if ($form->onSubmit()) {
    $data = $form->getData();
    
    // Sprawdzenie czy nazwa użytkownika jest dostępna
    if (isUsernameTaken($data['username'])) {
        $form->addValidation('username', 'Ta nazwa użytkownika jest już zajęta');
    } else {
        // Nazwa dostępna
        echo "Nazwa użytkownika jest dostępna!";
    }
}

echo $form->render();
```

## Przykłady użycia

### Podstawowa walidacja formularza

```php
use NimblePHP\Form\Form;

$form = new Form('/register');
$form->setId('registration-form');

$form->addInput('first_name', 'Imię')
     ->addInput('last_name', 'Nazwisko')
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addInput('password', 'Hasło', ['type' => 'password'])
     ->addCheckbox('terms', 'Akceptuję regulamin')
     ->addSubmitButton('Zarejestruj się');

// Reguły walidacji
$form->validation([
    'first_name' => ['required', 'length' => ['min' => 2, 'max' => 50]],
    'last_name' => ['required', 'length' => ['min' => 2, 'max' => 50]],
    'email' => ['required', 'isEmail'],
    'password' => ['required', 'length' => ['min' => 8]],
    'terms' => ['checked']
]);

if ($form->onSubmit()) {
    $data = $form->getData();
    
    // Dodatkowa walidacja biznesowa
    if (emailExists($data['email'])) {
        $form->addValidation('email', 'Ten adres email jest już zajęty');
    }
    
    if (empty($form->validationErrors)) {
        // Rejestracja użytkownika
        registerUser($data);
        echo "Rejestracja zakończona sukcesem!";
    }
}

echo $form->render();
```

### Walidacja z niestandardowymi regułami

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Exceptions\ValidationException;

$form = new Form('/contact');
$form->addInput('name', 'Imię')
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addTextarea('message', 'Wiadomość')
     ->addSubmitButton('Wyślij');

$form->validation([
    'name' => [
        'required',
        'length' => ['min' => 2],
        function($value) {
            // Sprawdzenie czy nazwa nie zawiera liczb
            if (preg_match('/\d/', $value)) {
                throw new ValidationException('Imię nie może zawierać liczb');
            }
        }
    ],
    'email' => [
        'required',
        'isEmail',
        function($value) {
            // Sprawdzenie czy email nie jest z domeny tymczasowej
            $tempDomains = ['10minutemail.com', 'tempmail.org'];
            $domain = substr(strrchr($value, "@"), 1);
            
            if (in_array($domain, $tempDomains)) {
                throw new ValidationException('Nie można używać tymczasowych adresów email');
            }
        }
    ],
    'message' => [
        'required',
        'length' => ['min' => 10, 'max' => 1000],
        function($value) {
            // Sprawdzenie czy wiadomość nie jest spamem
            $spamWords = ['viagra', 'casino', 'lottery'];
            
            foreach ($spamWords as $word) {
                if (stripos($value, $word) !== false) {
                    throw new ValidationException('Wiadomość zawiera niedozwoloną treść');
                }
            }
        }
    ]
]);

if ($form->onSubmit()) {
    $data = $form->getData();
    sendContactMessage($data);
    echo "Wiadomość została wysłana!";
}

echo $form->render();
```

### Walidacja warunkowa

```php
$form = new Form('/profile');
$form->addSelect('account_type', [
         'personal' => 'Konto osobiste',
         'business' => 'Konto biznesowe'
     ], null, 'Typ konta')
     ->addInput('name', 'Imię i nazwisko')
     ->addInput('company_name', 'Nazwa firmy')
     ->addInput('tax_id', 'NIP')
     ->addSubmitButton('Zapisz');

if ($form->onSubmit()) {
    $data = $form->getData();
    
    // Walidacja podstawowa
    $form->validation([
        'account_type' => ['required'],
        'name' => ['required']
    ]);
    
    // Walidacja warunkowa dla kont biznesowych
    if ($data['account_type'] === 'business') {
        if (empty($data['company_name'])) {
            $form->addValidation('company_name', 'Nazwa firmy jest wymagana dla kont biznesowych');
        }
        
        if (empty($data['tax_id'])) {
            $form->addValidation('tax_id', 'NIP jest wymagany dla kont biznesowych');
        } elseif (!isValidTaxId($data['tax_id'])) {
            $form->addValidation('tax_id', 'Nieprawidłowy format NIP');
        }
    }
    
    if (empty($form->validationErrors)) {
        saveProfile($data);
        echo "Profil został zapisany!";
    }
}

echo $form->render();
```

### Walidacja z bazą danych

```php
use NimblePHP\Form\Form;

class UserRegistrationForm extends Form
{
    private $userModel;
    
    public function __construct()
    {
        parent::__construct('/register');
        $this->userModel = new UserModel();
        
        $this->addInput('username', 'Nazwa użytkownika')
             ->addInput('email', 'Email', ['type' => 'email'])
             ->addInput('password', 'Hasło', ['type' => 'password'])
             ->addSubmitButton('Zarejestruj się');
    }
    
    public function validateForm(): bool
    {
        // Podstawowa walidacja
        $this->validation([
            'username' => ['required', 'length' => ['min' => 3, 'max' => 20]],
            'email' => ['required', 'isEmail'],
            'password' => ['required', 'length' => ['min' => 8]]
        ]);
        
        if ($this->onSubmit()) {
            $data = $this->getData();
            
            // Sprawdzenie unikalności nazwy użytkownika
            if ($this->userModel->findByUsername($data['username'])) {
                $this->addValidation('username', 'Ta nazwa użytkownika jest już zajęta');
            }
            
            // Sprawdzenie unikalności email
            if ($this->userModel->findByEmail($data['email'])) {
                $this->addValidation('email', 'Ten adres email jest już zarejestrowany');
            }
            
            // Sprawdzenie siły hasła
            if (!$this->isStrongPassword($data['password'])) {
                $this->addValidation('password', 'Hasło musi zawierać wielką literę, małą literę i cyfrę');
            }
            
            return empty($this->validationErrors);
        }
        
        return false;
    }
    
    private function isStrongPassword(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password);
    }
}

// Użycie
$form = new UserRegistrationForm();

if ($form->validateForm()) {
    $data = $form->getData();
    // Rejestracja użytkownika
    echo "Użytkownik został zarejestrowany!";
} else {
    echo $form->render();
}
```

### Walidacja wieloetapowa

```php
$form = new Form('/checkout');
$form->setId('checkout-form');

// Etap 1: Dane osobowe
$form->title('Dane osobowe')
     ->addInput('first_name', 'Imię')
     ->addInput('last_name', 'Nazwisko')
     ->addInput('email', 'Email', ['type' => 'email'])
     
     // Etap 2: Adres
     ->title('Adres dostawy')
     ->addInput('street', 'Ulica')
     ->addInput('city', 'Miasto')
     ->addInput('postal_code', 'Kod pocztowy')
     
     // Etap 3: Płatność
     ->title('Płatność')
     ->addSelect('payment_method', [
         'card' => 'Karta płatnicza',
         'transfer' => 'Przelew bankowy',
         'cod' => 'Płatność przy odbiorze'
     ], null, 'Metoda płatności')
     ->addSubmitButton('Złóż zamówienie');

if ($form->onSubmit()) {
    $data = $form->getData();
    $step = $data['step'] ?? 1;
    
    switch ($step) {
        case 1:
            // Walidacja danych osobowych
            $form->validation([
                'first_name' => ['required'],
                'last_name' => ['required'],
                'email' => ['required', 'isEmail']
            ]);
            break;
            
        case 2:
            // Walidacja adresu
            $form->validation([
                'street' => ['required'],
                'city' => ['required'],
                'postal_code' => ['required', 'length' => ['min' => 5, 'max' => 6]]
            ]);
            break;
            
        case 3:
            // Walidacja płatności
            $form->validation([
                'payment_method' => ['required']
            ]);
            
            if ($data['payment_method'] === 'card') {
                if (empty($data['card_number'])) {
                    $form->addValidation('card_number', 'Numer karty jest wymagany');
                }
            }
            break;
    }
    
    if (empty($form->validationErrors)) {
        if ($step < 3) {
            // Przejście do kolejnego etapu
            $form->addInputHidden('step', $step + 1);
        } else {
            // Finalizacja zamówienia
            processOrder($data);
            echo "Zamówienie zostało złożone!";
        }
    }
}

echo $form->render();
```

### Dostęp do globalnych błędów walidacji

```php
use NimblePHP\Form\Form;

// Formularz 1
$form1 = new Form('/form1');
$form1->addInput('field1', 'Pole 1')->addSubmitButton('Wyślij');
$form1->validation(['field1' => ['required']]);

// Formularz 2
$form2 = new Form('/form2');
$form2->addInput('field2', 'Pole 2')->addSubmitButton('Wyślij');
$form2->validation(['field2' => ['required']]);

// Sprawdzenie globalnych błędów walidacji
$globalErrors = Form::$VALIDATIONS;

if (!empty($globalErrors)) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>Błędy walidacji:</h4>";
    echo "<ul>";
    foreach ($globalErrors as $field => $error) {
        echo "<li>{$field}: {$error}</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo $form1->render();
echo $form2->render();
```

## Integracja z klasą Validation

Trait automatycznie tworzy instancję klasy `NimblePHP\Form\Validation` i przekazuje jej:
- Reguły walidacji
- Dane formularza
- Uruchamia walidację
- Zbiera błędy

## Cykl życia walidacji

1. **Przygotowanie danych** - `prepareData()`
2. **Sprawdzenie ID formularza** - weryfikacja `formId`
3. **Utworzenie walidatora** - instancja `Validation`
4. **Uruchomienie walidacji** - `validation->run()`
5. **Zebranie błędów** - aktualizacja `$validationErrors`
6. **Aktualizacja globalnych błędów** - `Form::$VALIDATIONS`

## Uwagi

1. **Automatyczne sprawdzanie**: Walidacja jest automatycznie uruchamiana w `onSubmit()`
2. **ID formularza**: Jeśli formularz ma ID, jest automatycznie weryfikowany
3. **Globalne błędy**: Wszystkie błędy są dostępne przez `Form::$VALIDATIONS`
4. **Fluent interface**: Metody można łączyć w łańcuch
5. **Kompatybilność**: Pełna integracja z klasą `Validation`

## Zobacz również

- [Form](Form.md) - Główna klasa formularza
- [Validation](Validation.md) - Klasa walidacji
- [ValidationException](ValidationException.md) - Wyjątek walidacji
- [Field](Field.md) - Trait do zarządzania polami