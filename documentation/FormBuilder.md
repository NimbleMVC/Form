# Klasa FormBuilder

Abstrakcyjna klasa bazowa do tworzenia builderów formularzy w aplikacjach NimblePHP. Zapewnia strukturę i funkcjonalność do budowania złożonych formularzy z walidacją i obsługą danych.

## Namespace

```php
NimblePHP\Form\FormBuilder
```

## Implementuje

- `NimblePHP\Form\Interfaces\FormBuilderInterface`

## Używane traity

- `NimblePHP\Framework\Traits\LoadModelTrait` - ładowanie modeli

## Właściwości

### `$form` (public Form)
Instancja formularza.

### `$method` (protected MethodEnum)
Metoda HTTP formularza. Domyślnie `MethodEnum::POST`.

### `$action` (protected ?string)
Akcja formularza (URL docelowy). Domyślnie `null`.

### `$controller` (public ?ControllerInterface)
Instancja kontrolera.

### `$request` (protected Request)
Instancja klasy Request.

### `$data` (protected array)
Dane wejściowe formularza.

### `$postData` (protected array)
Dane POST z żądania.

## Konstruktor

### `__construct(?ControllerInterface $controller = null)`

Inicjalizuje builder formularza.

**Parametry:**
- `$controller` - instancja kontrolera (opcjonalna)

**Przykład:**
```php
class ContactFormBuilder extends FormBuilder
{
    protected MethodEnum $method = MethodEnum::POST;
    protected ?string $action = '/contact/send';
    
    public function __construct(?ControllerInterface $controller = null)
    {
        parent::__construct($controller);
    }
}
```

## Metody statyczne

### `generate(string $name, ?ControllerInterface $controller = null, array $data = []): string`

Generuje i renderuje formularz na podstawie nazwy buildera.

**Parametry:**
- `$name` - nazwa buildera formularza (ścieżka w namespace App\Form)
- `$controller` - instancja kontrolera (opcjonalna)
- `$data` - dane do przekazania do formularza

**Zwraca:** string - wyrenderowany HTML formularza

**Rzuca:**
- `NotFoundException` - gdy builder nie zostanie znaleziony
- `NimbleException` - błąd frameworka

**Przykład:**
```php
// Wygenerowanie formularza ContactFormBuilder z App\Form\ContactFormBuilder
echo FormBuilder::generate('ContactFormBuilder', $this);

// Formularz w podfolderze
echo FormBuilder::generate('User/RegistrationFormBuilder', $this, ['role' => 'user']);
```

## Metody abstrakcyjne (do implementacji)

### `onSubmit(): void`

Metoda wywoływana po poprawnym wysłaniu formularza. Musi być zaimplementowana w klasie dziedziczącej.

**Przykład:**
```php
public function onSubmit(): void
{
    $data = $this->form->getData();
    
    // Ładowanie modelu
    $userModel = $this->loadModel(User::class);
    
    // Zapis danych
    $userModel->create([
        'name' => $data['name'],
        'email' => $data['email']
    ]);
    
    // Przekierowanie lub komunikat
    $this->controller->redirect('/success');
}
```

### `init(): void`

Metoda inicjalizacji buildera. Wywoływana przed `create()`.

**Przykład:**
```php
public function init(): void
{
    $this->form->setId('contact-form');
    
    // Ustawienie danych domyślnych
    if ($this->controller) {
        $userId = $this->controller->getSession('user_id');
        if ($userId) {
            $userModel = $this->loadModel(User::class);
            $user = $userModel->find($userId);
            $this->form->setData([
                'name' => $user->name,
                'email' => $user->email
            ]);
        }
    }
}
```

### `create(): void`

Metoda tworzenia struktury formularza. Tutaj dodawane są pola formularza.

**Przykład:**
```php
public function create(): void
{
    $this->form->addInput('name', 'Imię i nazwisko')
               ->addInput('email', 'Email', ['type' => 'email'])
               ->addTextarea('message', 'Wiadomość')
               ->addSubmitButton('Wyślij');
}
```

### `validation(): array`

Metoda zwracająca reguły walidacji formularza.

**Zwraca:** array - tablica z regułami walidacji

**Przykład:**
```php
public function validation(): array
{
    return [
        'name' => ['required', 'length' => ['min' => 2]],
        'email' => ['required', 'isEmail'],
        'message' => ['required', 'length' => ['min' => 10, 'max' => 1000]]
    ];
}
```

## Metody publiczne

### `addError(string $name, string $error): void`

Dodaje błąd walidacji do określonego pola.

**Parametry:**
- `$name` - nazwa pola
- `$error` - treść błędu

**Przykład:**
```php
public function onSubmit(): void
{
    $data = $this->form->getData();
    
    $userModel = $this->loadModel(User::class);
    
    // Sprawdzenie czy email już istnieje
    if ($userModel->findByEmail($data['email'])) {
        $this->addError('email', 'Ten adres email jest już zajęty');
        return;
    }
    
    // Kontynuacja przetwarzania...
}
```

### `log(string $message, string $level = 'INFO', array $content = []): bool`

Tworzy wpis w logach aplikacji.

**Parametry:**
- `$message` - wiadomość do zalogowania
- `$level` - poziom loga (domyślnie 'INFO')
- `$content` - dodatkowe dane do zalogowania

**Zwraca:** bool - true jeśli log został utworzony

**Rzuca:** Exception - błąd podczas logowania

**Przykład:**
```php
public function onSubmit(): void
{
    $data = $this->form->getData();
    
    try {
        $userModel = $this->loadModel(User::class);
        $user = $userModel->create($data);
        
        $this->log('User registered successfully', 'INFO', [
            'user_id' => $user->id,
            'email' => $data['email']
        ]);
        
    } catch (Exception $e) {
        $this->log('User registration failed', 'ERROR', [
            'error' => $e->getMessage(),
            'data' => $data
        ]);
    }
}
```

## Przykłady użycia

### Prosty formularz kontaktowy

```php
<?php

namespace App\Form;

use NimblePHP\Form\FormBuilder;
use NimblePHP\Form\Enum\MethodEnum;
use App\Models\ContactMessage;

class ContactFormBuilder extends FormBuilder
{
    protected MethodEnum $method = MethodEnum::POST;
    protected ?string $action = '/contact/send';

    public function init(): void
    {
        $this->form->setId('contact-form');
    }

    public function create(): void
    {
        $this->form->addInput('name', 'Imię i nazwisko')
                   ->addInput('email', 'Email', ['type' => 'email'])
                   ->addInput('phone', 'Telefon', ['type' => 'tel'])
                   ->addTextarea('message', 'Wiadomość', ['rows' => 5])
                   ->addSubmitButton('Wyślij wiadomość');
    }

    public function validation(): array
    {
        return [
            'name' => ['required', 'length' => ['min' => 2]],
            'email' => ['required', 'isEmail'],
            'message' => ['required', 'length' => ['min' => 10, 'max' => 1000]]
        ];
    }

    public function onSubmit(): void
    {
        $data = $this->form->getData();
        
        // Ładowanie modelu
        $contactModel = $this->loadModel(ContactMessage::class);
        
        // Zapis wiadomości
        $message = $contactModel->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Logowanie
        $this->log('Contact message received', 'INFO', [
            'message_id' => $message->id,
            'sender_email' => $data['email']
        ]);
        
        // Przekierowanie
        if ($this->controller) {
            $this->controller->setFlash('success', 'Dziękujemy za wiadomość!');
            $this->controller->redirect('/contact/success');
        }
    }
}
```

**Użycie w kontrolerze:**
```php
public function contactAction(): string
{
    return FormBuilder::generate('ContactFormBuilder', $this);
}
```

### Formularz rejestracji użytkownika

```php
<?php

namespace App\Form;

use NimblePHP\Form\FormBuilder;
use NimblePHP\Form\Enum\MethodEnum;
use App\Models\User;

class UserRegistrationFormBuilder extends FormBuilder
{
    protected MethodEnum $method = MethodEnum::POST;
    protected ?string $action = '/auth/register';

    public function init(): void
    {
        $this->form->setId('registration-form');
    }

    public function create(): void
    {
        $this->form->title('Rejestracja użytkownika')
                   ->startGroup(6)
                   ->addInput('first_name', 'Imię')
                   ->addInput('last_name', 'Nazwisko')
                   ->stopGroup()
                   ->startGroup(6)
                   ->addInput('email', 'Email', ['type' => 'email'])
                   ->addInput('phone', 'Telefon', ['type' => 'tel'])
                   ->stopGroup()
                   ->addInput('password', 'Hasło', ['type' => 'password'])
                   ->addInput('password_confirm', 'Potwierdź hasło', ['type' => 'password'])
                   ->addCheckbox('terms', 'Akceptuję regulamin')
                   ->addCheckbox('newsletter', 'Zapisz mnie do newslettera')
                   ->addSubmitButton('Zarejestruj się');
    }

    public function validation(): array
    {
        return [
            'first_name' => ['required', 'length' => ['min' => 2]],
            'last_name' => ['required', 'length' => ['min' => 2]],
            'email' => ['required', 'isEmail'],
            'password' => ['required', 'length' => ['min' => 8]],
            'password_confirm' => ['required'],
            'terms' => ['checked']
        ];
    }

    public function onSubmit(): void
    {
        $data = $this->form->getData();
        
        // Sprawdzenie czy hasła się zgadzają
        if ($data['password'] !== $data['password_confirm']) {
            $this->addError('password_confirm', 'Hasła nie są identyczne');
            return;
        }
        
        $userModel = $this->loadModel(User::class);
        
        // Sprawdzenie czy email już istnieje
        if ($userModel->findByEmail($data['email'])) {
            $this->addError('email', 'Ten adres email jest już zajęty');
            return;
        }
        
        try {
            // Utworzenie użytkownika
            $user = $userModel->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'newsletter' => !empty($data['newsletter']),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->log('User registered successfully', 'INFO', [
                'user_id' => $user->id,
                'email' => $data['email']
            ]);
            
            if ($this->controller) {
                $this->controller->setFlash('success', 'Konto zostało utworzone!');
                $this->controller->redirect('/auth/login');
            }
            
        } catch (Exception $e) {
            $this->log('User registration failed', 'ERROR', [
                'error' => $e->getMessage(),
                'email' => $data['email']
            ]);
            
            $this->addError('email', 'Wystąpił błąd podczas rejestracji');
        }
    }
}
```

### Formularz edycji z danymi domyślnymi

```php
<?php

namespace App\Form\User;

use NimblePHP\Form\FormBuilder;
use NimblePHP\Form\Enum\MethodEnum;
use App\Models\User;

class EditFormBuilder extends FormBuilder
{
    protected MethodEnum $method = MethodEnum::POST;
    
    public function init(): void
    {
        $this->form->setId('user-edit-form');
        
        // Ładowanie danych użytkownika
        if (isset($this->data['user_id'])) {
            $userModel = $this->loadModel(User::class);
            $user = $userModel->find($this->data['user_id']);
            
            if ($user) {
                $this->form->setData([
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'newsletter' => $user->newsletter
                ]);
            }
        }
    }

    public function create(): void
    {
        $this->form->addInputHidden('user_id', $this->data['user_id'] ?? '')
                   ->addInput('first_name', 'Imię')
                   ->addInput('last_name', 'Nazwisko')
                   ->addInput('email', 'Email', ['type' => 'email'])
                   ->addInput('phone', 'Telefon', ['type' => 'tel'])
                   ->addCheckbox('newsletter', 'Newsletter')
                   ->addSubmitButton('Zapisz zmiany');
    }

    public function validation(): array
    {
        return [
            'user_id' => ['required', 'isInteger'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'isEmail']
        ];
    }

    public function onSubmit(): void
    {
        $data = $this->form->getData();
        
        $userModel = $this->loadModel(User::class);
        $user = $userModel->find($data['user_id']);
        
        if (!$user) {
            $this->addError('user_id', 'Użytkownik nie został znaleziony');
            return;
        }
        
        // Sprawdzenie czy email nie jest zajęty przez innego użytkownika
        $existingUser = $userModel->findByEmail($data['email']);
        if ($existingUser && $existingUser->id != $user->id) {
            $this->addError('email', 'Ten adres email jest już zajęty');
            return;
        }
        
        // Aktualizacja danych
        $userModel->update($user->id, [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'newsletter' => !empty($data['newsletter']),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->log('User updated', 'INFO', ['user_id' => $user->id]);
        
        if ($this->controller) {
            $this->controller->setFlash('success', 'Dane zostały zaktualizowane');
            $this->controller->redirect('/user/profile');
        }
    }
}
```

**Użycie w kontrolerze:**
```php
public function editUserAction(int $userId): string
{
    return FormBuilder::generate('User/EditFormBuilder', $this, [
        'user_id' => $userId
    ]);
}
```

## Uwagi

1. **Ładowanie modeli**: Używaj `$this->loadModel(Model::class)` do ładowania modeli
2. **Dependency Injection**: Builder automatycznie korzysta z DI kontenera
3. **Struktura katalogów**: Buildery powinny być w namespace `App\Form`
4. **Fluent Interface**: Metody formularza można łączyć w łańcuch
5. **Logowanie**: Używaj metody `log()` do śledzenia operacji
6. **Obsługa błędów**: Używaj `addError()` do dodawania błędów walidacji

## Zobacz również

- [Form](Form.md) - Główna klasa formularza
- [FormBuilderInterface](FormBuilderInterface.md) - Interfejs buildera
- [Validation](Validation.md) - Klasa walidacji
- [Field](Field.md) - Trait do zarządzania polami