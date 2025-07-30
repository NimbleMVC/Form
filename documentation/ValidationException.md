# Klasa ValidationException

Wyjątek rzucany podczas błędów walidacji w bibliotece NimblePHP Form. Dziedziczy po `NimbleException` z frameworka NimblePHP.

## Namespace

```php
NimblePHP\Form\Exceptions\ValidationException
```

## Dziedziczenie

```php
ValidationException extends NimbleException
```

## Przeznaczenie

`ValidationException` jest używany przez:
- Własne walidatory (funkcje anonimowe)
- Predefiniowane walidatory w klasie `Validation`
- Niestandardowe reguły walidacji

## Przykłady użycia

### Podstawowe użycie w własnych walidatorach

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Exceptions\ValidationException;

$form = new Form();
$form->addInput('password', 'Hasło', ['type' => 'password'])
     ->addSubmitButton('Wyślij');

$form->validation([
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
            
            if (!preg_match('/[!@#$%^&*]/', $value)) {
                throw new ValidationException('Hasło musi zawierać znak specjalny');
            }
        }
    ]
]);

if ($form->onSubmit()) {
    echo "Hasło jest poprawne!";
}

echo $form->render();
```

### Walidacja z bazą danych

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Exceptions\ValidationException;

$form = new Form('/register');
$form->addInput('username', 'Nazwa użytkownika')
     ->addInput('email', 'Email', ['type' => 'email'])
     ->addSubmitButton('Zarejestruj się');

$form->validation([
    'username' => [
        'required',
        'length' => ['min' => 3, 'max' => 20],
        function($value) {
            // Sprawdzenie czy nazwa użytkownika jest dostępna
            $userModel = new UserModel();
            if ($userModel->findByUsername($value)) {
                throw new ValidationException('Ta nazwa użytkownika jest już zajęta');
            }
            
            // Sprawdzenie czy nazwa nie zawiera niedozwolonych znaków
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                throw new ValidationException('Nazwa użytkownika może zawierać tylko litery, cyfry i podkreślenia');
            }
        }
    ],
    'email' => [
        'required',
        'isEmail',
        function($value) {
            // Sprawdzenie czy email nie jest już zarejestrowany
            $userModel = new UserModel();
            if ($userModel->findByEmail($value)) {
                throw new ValidationException('Ten adres email jest już zarejestrowany');
            }
            
            // Sprawdzenie czy email nie jest z zakazanej domeny
            $bannedDomains = ['tempmail.org', '10minutemail.com'];
            $domain = substr(strrchr($value, "@"), 1);
            
            if (in_array($domain, $bannedDomains)) {
                throw new ValidationException('Nie można używać tymczasowych adresów email');
            }
        }
    ]
]);

if ($form->onSubmit()) {
    $data = $form->getData();
    // Rejestracja użytkownika
    echo "Użytkownik został zarejestrowany!";
}

echo $form->render();
```

### Walidacja biznesowa

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Exceptions\ValidationException;

$form = new Form('/order');
$form->addInput('product_id', 'ID produktu', ['type' => 'number'])
     ->addInput('quantity', 'Ilość', ['type' => 'number'])
     ->addInput('coupon_code', 'Kod kuponu')
     ->addSubmitButton('Złóż zamówienie');

$form->validation([
    'product_id' => [
        'required',
        'isInteger',
        function($value) {
            $productModel = new ProductModel();
            $product = $productModel->find($value);
            
            if (!$product) {
                throw new ValidationException('Produkt nie został znaleziony');
            }
            
            if (!$product->isAvailable()) {
                throw new ValidationException('Produkt jest obecnie niedostępny');
            }
        }
    ],
    'quantity' => [
        'required',
        'isInteger',
        function($value) use ($form) {
            if ($value <= 0) {
                throw new ValidationException('Ilość musi być większa od zera');
            }
            
            $productId = $form->getDataByKey('product_id');
            if ($productId) {
                $productModel = new ProductModel();
                $product = $productModel->find($productId);
                
                if ($product && $value > $product->stock) {
                    throw new ValidationException("Dostępna ilość: {$product->stock}");
                }
                
                if ($product && $value > $product->max_order_quantity) {
                    throw new ValidationException("Maksymalna ilość zamówienia: {$product->max_order_quantity}");
                }
            }
        }
    ],
    'coupon_code' => [
        function($value) {
            if (!empty($value)) {
                $couponModel = new CouponModel();
                $coupon = $couponModel->findByCode($value);
                
                if (!$coupon) {
                    throw new ValidationException('Nieprawidłowy kod kuponu');
                }
                
                if ($coupon->isExpired()) {
                    throw new ValidationException('Kod kuponu wygasł');
                }
                
                if ($coupon->isUsed()) {
                    throw new ValidationException('Kod kuponu został już wykorzystany');
                }
                
                if ($coupon->min_order_value > 0) {
                    $orderValue = calculateOrderValue();
                    if ($orderValue < $coupon->min_order_value) {
                        throw new ValidationException("Minimalna wartość zamówienia: {$coupon->min_order_value} PLN");
                    }
                }
            }
        }
    ]
]);

if ($form->onSubmit()) {
    $data = $form->getData();
    processOrder($data);
    echo "Zamówienie zostało złożone!";
}

echo $form->render();
```

### Walidacja plików

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Exceptions\ValidationException;

$form = new Form('/upload', MethodEnum::POST);
$form->addField('input', 'avatar', 'Zdjęcie profilowe', ['type' => 'file', 'accept' => 'image/*'])
     ->addSubmitButton('Prześlij');

$form->validation([
    'avatar' => [
        function($value) {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                
                // Sprawdzenie rozmiaru pliku (max 2MB)
                if ($file['size'] > 2 * 1024 * 1024) {
                    throw new ValidationException('Plik nie może być większy niż 2MB');
                }
                
                // Sprawdzenie typu MIME
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mimeType, $allowedTypes)) {
                    throw new ValidationException('Dozwolone formaty: JPEG, PNG, GIF, WebP');
                }
                
                // Sprawdzenie wymiarów obrazu
                $imageInfo = getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    throw new ValidationException('Plik nie jest prawidłowym obrazem');
                }
                
                list($width, $height) = $imageInfo;
                if ($width < 100 || $height < 100) {
                    throw new ValidationException('Minimalne wymiary obrazu: 100x100 pikseli');
                }
                
                if ($width > 2000 || $height > 2000) {
                    throw new ValidationException('Maksymalne wymiary obrazu: 2000x2000 pikseli');
                }
                
            } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                throw new ValidationException('Błąd podczas przesyłania pliku');
            }
        }
    ]
]);

if ($form->onSubmit()) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        // Przetwarzanie pliku
        echo "Plik został przesłany pomyślnie!";
    }
}

echo $form->render();
```

### Walidacja wielopolowa

```php
use NimblePHP\Form\Form;
use NimblePHP\Form\Exceptions\ValidationException;

$form = new Form('/booking');
$form->addInput('check_in', 'Data zameldowania', ['type' => 'date'])
     ->addInput('check_out', 'Data wymeldowania', ['type' => 'date'])
     ->addInput('guests', 'Liczba gości', ['type' => 'number'])
     ->addInput('room_type', 'Typ pokoju')
     ->addSubmitButton('Zarezerwuj');

$form->validation([
    'check_in' => [
        'required',
        function($value) {
            $checkIn = new DateTime($value);
            $today = new DateTime();
            
            if ($checkIn < $today) {
                throw new ValidationException('Data zameldowania nie może być wcześniejsza niż dzisiaj');
            }
            
            if ($checkIn > (new DateTime())->add(new DateInterval('P1Y'))) {
                throw new ValidationException('Można rezerwować maksymalnie rok do przodu');
            }
        }
    ],
    'check_out' => [
        'required',
        function($value) use ($form) {
            $checkInValue = $form->getDataByKey('check_in');
            
            if ($checkInValue) {
                $checkIn = new DateTime($checkInValue);
                $checkOut = new DateTime($value);
                
                if ($checkOut <= $checkIn) {
                    throw new ValidationException('Data wymeldowania musi być późniejsza niż zameldowania');
                }
                
                $diff = $checkIn->diff($checkOut);
                if ($diff->days > 30) {
                    throw new ValidationException('Maksymalny okres pobytu: 30 dni');
                }
                
                if ($diff->days < 1) {
                    throw new ValidationException('Minimalny okres pobytu: 1 dzień');
                }
            }
        }
    ],
    'guests' => [
        'required',
        'isInteger',
        function($value) use ($form) {
            if ($value < 1) {
                throw new ValidationException('Liczba gości musi być większa od zera');
            }
            
            if ($value > 10) {
                throw new ValidationException('Maksymalna liczba gości: 10');
            }
            
            $roomType = $form->getDataByKey('room_type');
            if ($roomType) {
                $roomCapacity = getRoomCapacity($roomType);
                if ($value > $roomCapacity) {
                    throw new ValidationException("Wybrany typ pokoju pomieści maksymalnie {$roomCapacity} gości");
                }
            }
        }
    ],
    'room_type' => [
        'required',
        function($value) use ($form) {
            $availableRooms = ['single', 'double', 'triple', 'suite'];
            
            if (!in_array($value, $availableRooms)) {
                throw new ValidationException('Nieprawidłowy typ pokoju');
            }
            
            $checkIn = $form->getDataByKey('check_in');
            $checkOut = $form->getDataByKey('check_out');
            
            if ($checkIn && $checkOut) {
                if (!isRoomAvailable($value, $checkIn, $checkOut)) {
                    throw new ValidationException('Wybrany typ pokoju nie jest dostępny w podanych terminach');
                }
            }
        }
    ]
]);

if ($form->onSubmit()) {
    $data = $form->getData();
    createBooking($data);
    echo "Rezerwacja została utworzona!";
}

echo $form->render();
```

### Obsługa wyjątków w FormBuilder

```php
use NimblePHP\Form\FormBuilder;
use NimblePHP\Form\Exceptions\ValidationException;

class CustomFormBuilder extends FormBuilder
{
    public function validation(): array
    {
        return [
            'email' => [
                'required',
                'isEmail',
                [$this, 'validateUniqueEmail']
            ],
            'age' => [
                'required',
                'isInteger',
                [$this, 'validateAge']
            ]
        ];
    }
    
    public function validateUniqueEmail($email)
    {
        $userModel = $this->loadModel(User::class);
        
        if ($userModel->findByEmail($email)) {
            throw new ValidationException('Ten adres email jest już zarejestrowany');
        }
    }
    
    public function validateAge($age)
    {
        if ($age < 18) {
            throw new ValidationException('Musisz mieć co najmniej 18 lat');
        }
        
        if ($age > 120) {
            throw new ValidationException('Podany wiek wydaje się nieprawidłowy');
        }
    }
    
    public function create(): void
    {
        $this->form->addInput('email', 'Email', ['type' => 'email'])
                   ->addInput('age', 'Wiek', ['type' => 'number'])
                   ->addSubmitButton('Wyślij');
    }
    
    public function onSubmit(): void
    {
        $data = $this->form->getData();
        
        try {
            // Dodatkowa walidacja biznesowa
            $this->validateBusinessRules($data);
            
            // Zapis danych
            $this->saveUser($data);
            
        } catch (ValidationException $e) {
            // Dodanie błędu do formularza
            $this->addError('general', $e->getMessage());
        }
    }
    
    private function validateBusinessRules(array $data): void
    {
        // Przykład złożonej walidacji biznesowej
        if ($data['age'] < 21 && $this->isAlcoholRelatedService()) {
            throw new ValidationException('Usługa dostępna tylko dla osób pełnoletnich (21+)');
        }
    }
}
```

## Uwagi

1. **Dziedziczenie**: `ValidationException` dziedziczy po `NimbleException`, więc ma wszystkie jego funkcjonalności

2. **Automatyczne przechwytywanie**: Wyjątki są automatycznie przechwytywane przez system walidacji

3. **Komunikaty**: Komunikat wyjątku staje się komunikatem błędu wyświetlanym użytkownikowi

4. **Przerwanie walidacji**: Rzucenie wyjątku przerywa walidację danego pola

5. **Bezpieczeństwo**: Komunikaty błędów są automatycznie escapowane przed wyświetleniem

## Zobacz również

- [Validation](Validation.md) - Klasa walidacji
- [ValidationTrait](ValidationTrait.md) - Trait walidacji
- [Form](Form.md) - Główna klasa formularza
- [FormBuilder](FormBuilder.md) - Builder formularzy