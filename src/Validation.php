<?php

namespace NimblePHP\Form;

use Krzysztofzylka\Arrays\Arrays;
use NimblePHP\Form\Exceptions\ValidationException;
use NimblePHP\Framework\Exception\NimbleException;

/**
 * Form validation
 */
class Validation
{

    /**
     * Error language
     * @var array
     */
    public static array $language = [
        'required' => 'This field cannot be empty.',
        'checked' => 'The checkbox must be checked.',
        'length_min' => 'The field cannot have fewer than {length} [character,characters,characters].',
        'length_max' => 'The field cannot have more than {length} [character,characters,characters].',
        'isEmail' => 'The provided email address is invalid.',
        'isInteger' => 'The provided value must be an integer.',
        'invalidInt' => 'Invalid numeric value.',
        'decimalMax' => 'The field may not have more than {decimal} [decimal place, decimal places].',
        'invalidEnum' => 'Incorrect value.'
    ];

    /**
     * Fields
     * @var array
     */
    protected array $fields = [];

    /**
     * POST or GET data
     * @var array
     */
    protected array $data;

    /**
     * Validation errors
     * @var array
     */
    protected array $validationErrors = [];

    /**
     * Construct
     * @param array $validationList
     * @param array $data
     */
    public function __construct(array $validationList, array $data)
    {
        $this->fields = $validationList;
        $this->data = $data;
    }

    /**
     * Change language
     * @param string $lang
     * @return void
     * @throws NimbleException
     */
    public static function changeLanguage(string $lang): void
    {
        if (!in_array($lang, ['PL'])) {
            throw new NimbleException('Language not supported.');
        }

        switch ($lang) {
            case 'PL':
                self::$language = array_merge(
                    self::$language,
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
                );
        }
    }

    /**
     * Run validation
     * @return array
     */
    public function run(): array
    {
        foreach ($this->fields as $fieldKey => $validationList) {
            foreach ($validationList as $validationType => $validation) {
                try {
                    if (is_callable($validation)) {
                        $validation($this->getDataByKey($fieldKey));
                    } elseif (is_string($validationType)) {
                        $this->predefinedValidation($validationType, $validation, $this->getDataByKey($fieldKey));
                    } elseif (is_int($validationType)) {
                        $this->predefinedValidation($validation, null, $this->getDataByKey($fieldKey));
                    }
                } catch (ValidationException $exception) {
                    $this->validationErrors[$fieldKey] = $exception->getMessage();

                    continue 2;
                }
            }
        }

        return $this->validationErrors;
    }

    /**
     * Predefined validations
     * @param string $name
     * @param mixed $customData
     * @param mixed $data
     * @return void
     * @throws ValidationException
     */
    protected function predefinedValidation(string $name, mixed $customData, mixed $data): void
    {
        switch ($name) {
            case 'required':
                if (is_null($data) || trim($data) === '') {
                    throw new ValidationException(self::$language['required']);
                }

                break;
            case 'checked':
                if (!(bool)trim($data)) {
                    throw new ValidationException(self::$language['checked']);
                }

                break;
            case 'length':
                if (is_array($customData) && (array_key_exists('min', $customData) || array_key_exists('max', $customData))) {
                    $min = $customData['min'] ?? null;
                    $max = $customData['max'] ?? null;

                    if ($min && strlen($data) < $min) {
                        $validation = str_replace('{length}', $min, self::$language['length_min']);

                        throw new ValidationException($this->replaceInflections($validation));
                    }

                    if ($max && strlen($data ?? '') > $max) {
                        $validation = str_replace('{length}', $max, self::$language['length_max']);

                        throw new ValidationException($this->replaceInflections($validation));
                    }
                }

                break;
            case 'isEmail':
                if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                    throw new ValidationException(self::$language['isEmail']);
                }

                break;
            case 'isInteger':
                if (!filter_var($data, FILTER_VALIDATE_INT)) {
                    throw new ValidationException(self::$language['isInteger']);
                }

                break;
            case 'isDecimal':
                $data = str_replace(',', '.', $data);

                if (!is_numeric($data)) {
                    throw new ValidationException(self::$language['invalidInt']);
                }

                if (!str_contains($data, '.')) {
                    return;
                }

                $maxPlaces = 2;

                if (is_array($customData) && array_key_exists('maxPlaces', $customData)) {
                    $maxPlaces = $customData['maxPlaces'];
                }

                $decimalPart = explode('.', $data)[1];

                if (strlen($decimalPart) > $maxPlaces) {
                    $validation = str_replace('{decimal}', $maxPlaces, self::$language['decimalMax']);

                    throw new ValidationException($this->replaceInflections($validation));
                }

                break;
            case 'enum':
                $names = array_column($customData::cases(), 'name');

                if (!in_array($data, $names)) {
                    throw new ValidationException(self::$language['invalidEnum']);
                }
                break;
        }
    }

    /**
     * Get data by key
     * @param string|null $name
     * @return ?string
     */
    protected function getDataByKey(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        return Arrays::getNestedValue($this->data, explode('/', $name));
    }

    /**
     * Inflected word
     * @param $number
     * @param $wordForms
     * @return string
     */
    protected function inflectWord($number, $wordForms): string
    {
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;

        if ($lastDigit == 1 && $lastTwoDigits != 11) {
            return $number . " " . $wordForms[0];
        } elseif (in_array($lastDigit, [2, 3, 4]) && !in_array($lastTwoDigits, [12, 13, 14])) {
            return $number . " " . $wordForms[1];
        } else {
            return $number . " " . $wordForms[2];
        }
    }

    /**
     * Replace inflected words
     * @param $text
     * @return string
     */
    protected function replaceInflections($text): string
    {
        return preg_replace_callback('/(\d+)\s*\[([^\]]+)\]/', function ($matches) {
            $number = $matches[1];
            $words = explode(',', $matches[2]);

            return $this->inflectWord($number, $words);
        }, $text);
    }

}