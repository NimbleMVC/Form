# Interfejs FormBuilderInterface

Interfejs definiujący kontrakt dla builderów formularzy w bibliotece NimblePHP Form. Określa metody, które muszą być zaimplementowane w klasach dziedziczących po `FormBuilder`.

## Namespace

```php
NimblePHP\Form\Interfaces\FormBuilderInterface
```

## Metody wymagane

### `onSubmit(): void`

Metoda wywoływana po poprawnym wysłaniu i walidacji formularza. Zawiera logikę przetwarzania danych formularza.

**Zwraca:** void

**Przykład implementacji:**
```php
public function onSubmit(): void
{
    $data = $this->form->getData();
    
    // Przetwarzanie danych
    $userModel = $this->loadModel(User::class);
    $user = $userModel->create([
        'name' => $data['name'],
        'email' => $data['email']
    ]);
    
    // Przekierowanie lub komunikat
    if ($this->controller) {
        $this->controller->setFlash('success', 'Dane zostały zapisane!');
        $this->controller->redirect('/success');
    }
}
```

### `init(): void`

Metoda inicjalizacji buildera. Wywoływana przed `create()`. Służy do konfiguracji formularza i ustawienia danych domyślnych.

**Zwraca:** void

**Przykład implementacji:**
```php
public function init(): void
{
    $this->form->setId('user-form');
    
    // Ustawienie danych domyślnych
    if (isset($this->data['user_id'])) {
        $userModel = $this->loadModel(User::class);
        $user = $userModel->find($this->data['user_id']);
        
        if ($user) {
            $this->form->setData([
                'name' => $user->name,
                'email' => $user->email
            ]);
        }
    }
}
```

### `create(): void`

Metoda definiująca strukturę formularza. Tutaj dodawane są wszystkie pola formularza.

**Zwraca:** void

**Przykład implementacji:**
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

Metoda zwracająca reguły walidacji dla formularza.

**Zwraca:** array - tablica z regułami walidacji

**Przykład implementacji:**
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

### `addError(string $name, string $error): void`

Metoda do dodawania błędów walidacji do określonych pól.

**Parametry:**
- `$name` - nazwa pola
- `$error` - komunikat błędu

**Zwraca:** void

**Przykład implementacji:**
```php
public function addError(string $name, string $error): void
{
    $this->form->validation([
        $name => [
            function () use ($error) {
                throw new ValidationException($error);
            }
        ]
    ]);
}
```

## Przykłady implementacji

### Prosty formularz kontaktowy

```php
<?php

namespace App\Form;

use NimblePHP\Form\FormBuilder;
use NimblePHP\Form\Interfaces\FormBuilderInterface;
use NimblePHP\Form\Enum\MethodEnum;

class ContactFormBuilder extends FormBuilder implements FormBuilderInterface
{
    protected MethodEnum $method = MethodEnum::POST;
    protected ?string $action = '/contact/send';

    public function init(): void
    {
        $this->form->setId('contact-form');
        
        // Jeśli użytkownik jest zalogowany, wypełnij dane
        if ($this->controller && $this->controller->getSession('user_id')) {
            $userModel = $this->loadModel(User::class);
            $user = $userModel->find($this->controller->getSession('user_id'));
            
            if ($user) {
                $this->form->setData([
                    'name' => $user->name,
                    'email' => $user->email
                ]);
            }
        }
    }

    public function create(): void
    {
        $this->form->addInput('name', 'Imię i nazwisko', ['required' => true])
                   ->addInput('email', 'Email', ['type' => 'email', 'required' => true])
                   ->addInput('phone', 'Telefon', ['type' => 'tel'])
                   ->addSelect('subject', [
                       'general' => 'Zapytanie ogólne',
                       'support' => 'Wsparcie techniczne',
                       'sales' => 'Sprzedaż'
                   ], null, 'Temat')
                   ->addTextarea('message', 'Wiadomość', [
                       'rows' => 5,
                       'required' => true,
                       'placeholder' => 'Opisz swoje zapytanie...'
                   ])
                   ->addCheckbox('newsletter', 'Chcę otrzymywać newsletter')
                   ->addSubmitButton('Wyślij wiadomość');
    }

    public function validation(): array
    {
        return [
            'name' => ['required', 'length' => ['min' => 2, 'max' => 100]],
            'email' => ['required', 'isEmail'],
            'subject' => ['required'],
            'message' => ['required', 'length' => ['min' => 10, 'max' => 2000]]
        ];
    }

    public function onSubmit(): void
    {
        $data = $this->form->getData();
        
        // Dodatkowa walidacja
        if ($this->isSpam($data['message'])) {
            $this->addError('message', 'Wiadomość została oznaczona jako spam');
            return;
        }
        
        // Zapis wiadomości
        $contactModel = $this->loadModel(ContactMessage::class);
        $message = $contactModel->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'newsletter' => !empty($data['newsletter']),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Wysłanie emaila
        $this->sendNotificationEmail($data);
        
        // Logowanie
        $this->log('Contact message received', 'INFO', [
            'message_id' => $message->id,
            'sender_email' => $data['email']
        ]);
        
        // Przekierowanie
        if ($this->controller) {
            $this->controller->setFlash('success', 'Dziękujemy za wiadomość! Odpowiemy najszybciej jak to możliwe.');
            $this->controller->redirect('/contact/success');
        }
    }
    
    private function isSpam(string $message): bool
    {
        $spamWords = ['viagra', 'casino', 'lottery', 'bitcoin'];
        
        foreach ($spamWords as $word) {
            if (stripos($message, $word) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function sendNotificationEmail(array $data): void
    {
        // Implementacja wysyłania emaila
        $emailService = new EmailService();
        $emailService->send([
            'to' => 'admin@example.com',
            'subject' => 'Nowa wiadomość kontaktowa: ' . $data['subject'],
            'template' => 'contact_notification',
            'data' => $data
        ]);
    }
}
```

### Formularz rejestracji użytkownika

```php
<?php

namespace App\Form;

use NimblePHP\Form\FormBuilder;
use NimblePHP\Form\Interfaces\FormBuilderInterface;
use NimblePHP\Form\Exceptions\ValidationException;

class UserRegistrationFormBuilder extends FormBuilder implements FormBuilderInterface
{
    public function init(): void
    {
        $this->form->setId('registration-form');
    }

    public function create(): void
    {
        $this->form->title('Rejestracja nowego użytkownika')
                   ->startGroup(6)
                   ->addInput('first_name', 'Imię', ['required' => true])
                   ->addInput('last_name', 'Nazwisko', ['required' => true])
                   ->stopGroup()
                   ->startGroup(6)
                   ->addInput('email', 'Email', ['type' => 'email', 'required' => true])
                   ->addInput('phone', 'Telefon', ['type' => 'tel'])
                   ->stopGroup()
                   ->addInput('username', 'Nazwa użytkownika', ['required' => true])
                   ->addInput('password', 'Hasło', ['type' => 'password', 'required' => true])
                   ->addInput('password_confirm', 'Potwierdź hasło', ['type' => 'password', 'required' => true])
                   ->addSelect('country', $this->getCountries(), 'PL', 'Kraj')
                   ->addCheckbox('terms', 'Akceptuję regulamin', ['required' => true])
                   ->addCheckbox('newsletter', 'Chcę otrzymywać newsletter')
                   ->addSubmitButton('Zarejestruj się');
    }

    public function validation(): array
    {
        return [
            'first_name' => ['required', 'length' => ['min' => 2, 'max' => 50]],
            'last_name' => ['required', 'length' => ['min' => 2, 'max' => 50]],
            'email' => [
                'required',
                'isEmail',
                function($value) {
                    $userModel = $this->loadModel(User::class);
                    if ($userModel->findByEmail($value)) {
                        throw new ValidationException('Ten adres email jest już zarejestrowany');
                    }
                }
            ],
            'username' => [
                'required',
                'length' => ['min' => 3, 'max' => 20],
                function($value) {
                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                        throw new ValidationException('Nazwa użytkownika może zawierać tylko litery, cyfry i podkreślenia');
                    }
                    
                    $userModel = $this->loadModel(User::class);
                    if ($userModel->findByUsername($value)) {
                        throw new ValidationException('Ta nazwa użytkownika jest już zajęta');
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
                function($value) {
                    $password = $this->form->getDataByKey('password');
                    if ($value !== $password) {
                        throw new ValidationException('Hasła nie są identyczne');
                    }
                }
            ],
            'terms' => ['checked']
        ];
    }

    public function onSubmit(): void
    {
        $data = $this->form->getData();
        
        try {
            // Utworzenie użytkownika
            $userModel = $this->loadModel(User::class);
            $user = $userModel->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'username' => $data['username'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'country' => $data['country'],
                'newsletter' => !empty($data['newsletter']),
                'email_verified' => false,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Wysłanie emaila weryfikacyjnego
            $this->sendVerificationEmail($user);
            
            // Logowanie
            $this->log('User registered', 'INFO', [
                'user_id' => $user->id,
                'email' => $data['email']
            ]);
            
            if ($this->controller) {
                $this->controller->setFlash('success', 'Konto zostało utworzone! Sprawdź email aby zweryfikować konto.');
                $this->controller->redirect('/auth/login');
            }
            
        } catch (\Exception $e) {
            $this->log('User registration failed', 'ERROR', [
                'error' => $e->getMessage(),
                'email' => $data['email']
            ]);
            
            $this->addError('email', 'Wystąpił błąd podczas rejestracji. Spróbuj ponownie.');
        }
    }
    
    private function getCountries(): array
    {
        return [
            'PL' => 'Polska',
            'DE' => 'Niemcy',
            'FR' => 'Francja',
            'GB' => 'Wielka Brytania',
            'US' => 'Stany Zjednoczone'
        ];
    }
    
    private function sendVerificationEmail($user): void
    {
        $token = bin2hex(random_bytes(32));
        
        // Zapisanie tokena w bazie
        $tokenModel = $this->loadModel(EmailVerificationToken::class);
        $tokenModel->create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ]);
        
        // Wysłanie emaila
        $emailService = new EmailService();
        $emailService->send([
            'to' => $user->email,
            'subject' => 'Weryfikacja adresu email',
            'template' => 'email_verification',
            'data' => [
                'user' => $user,
                'verification_url' => url('/auth/verify/' . $token)
            ]
        ]);
    }
}
```

### Formularz edycji profilu

```php
<?php

namespace App\Form;

use NimblePHP\Form\FormBuilder;
use NimblePHP\Form\Interfaces\FormBuilderInterface;

class ProfileEditFormBuilder extends FormBuilder implements FormBuilderInterface
{
    public function init(): void
    {
        $this->form->setId('profile-edit-form');
        
        // Ładowanie danych użytkownika
        if (isset($this->data['user_id'])) {
            $userModel = $this->loadModel(User::class);
            $user = $userModel->find($this->data['user_id']);
            
            if ($user) {
                $profileModel = $this->loadModel(UserProfile::class);
                $profile = $profileModel->findByUserId($user->id);
                
                $this->form->setData([
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'bio' => $profile->bio ?? '',
                    'website' => $profile->website ?? '',
                    'location' => $profile->location ?? '',
                    'birth_date' => $profile->birth_date ?? '',
                    'newsletter' => $user->newsletter
                ]);
            }
        }
    }

    public function create(): void
    {
        $this->form->addInputHidden('user_id', $this->data['user_id'] ?? '')
                   
                   ->title('Dane podstawowe')
                   ->startGroup(6)
                   ->addInput('first_name', 'Imię', ['required' => true])
                   ->addInput('last_name', 'Nazwisko', ['required' => true])
                   ->stopGroup()
                   ->startGroup(6)
                   ->addInput('email', 'Email', ['type' => 'email', 'required' => true])
                   ->addInput('phone', 'Telefon', ['type' => 'tel'])
                   ->stopGroup()
                   
                   ->title('Informacje dodatkowe')
                   ->addTextarea('bio', 'O sobie', ['rows' => 4, 'maxlength' => 500])
                   ->startGroup(6)
                   ->addInput('website', 'Strona internetowa', ['type' => 'url'])
                   ->addInput('location', 'Lokalizacja')
                   ->stopGroup()
                   ->startGroup(6)
                   ->addInput('birth_date', 'Data urodzenia', ['type' => 'date'])
                   ->stopGroup()
                   
                   ->title('Ustawienia')
                   ->addCheckbox('newsletter', 'Otrzymuj newsletter')
                   
                   ->addSubmitButton('Zapisz zmiany', ['class' => 'btn btn-primary']);
    }

    public function validation(): array
    {
        return [
            'user_id' => ['required', 'isInteger'],
            'first_name' => ['required', 'length' => ['min' => 2, 'max' => 50]],
            'last_name' => ['required', 'length' => ['min' => 2, 'max' => 50]],
            'email' => [
                'required',
                'isEmail',
                function($value) {
                    $userId = $this->form->getDataByKey('user_id');
                    $userModel = $this->loadModel(User::class);
                    
                    $existingUser = $userModel->findByEmail($value);
                    if ($existingUser && $existingUser->id != $userId) {
                        throw new ValidationException('Ten adres email jest już zajęty');
                    }
                }
            ],
            'website' => [
                function($value) {
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                        throw new ValidationException('Nieprawidłowy adres URL');
                    }
                }
            ],
            'birth_date' => [
                function($value) {
                    if (!empty($value)) {
                        $birthDate = new \DateTime($value);
                        $today = new \DateTime();
                        
                        if ($birthDate > $today) {
                            throw new ValidationException('Data urodzenia nie może być w przyszłości');
                        }
                        
                        $age = $today->diff($birthDate)->y;
                        if ($age < 13) {
                            throw new ValidationException('Musisz mieć co najmniej 13 lat');
                        }
                    }
                }
            ]
        ];
    }

    public function onSubmit(): void
    {
        $data = $this->form->getData();
        
        try {
            $userModel = $this->loadModel(User::class);
            $profileModel = $this->loadModel(UserProfile::class);
            
            $user = $userModel->find($data['user_id']);
            if (!$user) {
                $this->addError('user_id', 'Użytkownik nie został znaleziony');
                return;
            }
            
            // Aktualizacja danych użytkownika
            $userModel->update($user->id, [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'newsletter' => !empty($data['newsletter']),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Aktualizacja profilu
            $profile = $profileModel->findByUserId($user->id);
            $profileData = [
                'bio' => $data['bio'] ?? null,
                'website' => $data['website'] ?? null,
                'location' => $data['location'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($profile) {
                $profileModel->update($profile->id, $profileData);
            } else {
                $profileData['user_id'] = $user->id;
                $profileData['created_at'] = date('Y-m-d H:i:s');
                $profileModel->create($profileData);
            }
            
            $this->log('Profile updated', 'INFO', ['user_id' => $user->id]);
            
            if ($this->controller) {
                $this->controller->setFlash('success', 'Profil został zaktualizowany');
                $this->controller->redirect('/profile');
            }
            
        } catch (\Exception $e) {
            $this->log('Profile update failed', 'ERROR', [
                'user_id' => $data['user_id'],
                'error' => $e->getMessage()
            ]);
            
            $this->addError('general', 'Wystąpił błąd podczas zapisywania. Spróbuj ponownie.');
        }
    }
}
```

## Uwagi implementacyjne

1. **Wszystkie metody są wymagane**: Każda klasa implementująca interfejs musi zdefiniować wszystkie metody

2. **Kolejność wykonania**: 
   - `init()` - inicjalizacja
   - `create()` - tworzenie struktury
   - `validation()` - definiowanie reguł
   - `onSubmit()` - przetwarzanie danych (tylko przy wysłaniu)

3. **Dostęp do danych**: Używaj `$this->form->getData()` w `onSubmit()`

4. **Obsługa błędów**: Używaj `addError()` do dodawania błędów walidacji

5. **Logowanie**: Używaj `$this->log()` do rejestrowania operacji

## Zobacz również

- [FormBuilder](FormBuilder.md) - Abstrakcyjna klasa bazowa
- [Form](Form.md) - Główna klasa formularza
- [Validation](Validation.md) - Walidacja danych
- [ValidationException](ValidationException.md) - Wyjątek walidacji