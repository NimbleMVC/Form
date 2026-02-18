<?php

namespace NimblePHP\Form;

use Krzysztofzylka\Arrays\Arrays;
use NimblePHP\Form\Exceptions\ValidationException;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Framework\Kernel;
use NimblePHP\Framework\Translation\Translation;

/**
 * Form validation
 */
class Validation
{

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
     * Translation instance
     * @var Translation
     */
    protected Translation $translation;

    /**
     * Construct
     * @param array $validationList
     * @param array $data
     */
    public function __construct(array $validationList, array $data)
    {
        $this->fields = $validationList;
        $this->data = $data;
        $this->translation = Kernel::$serviceContainer->get('translation');
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
                    throw new ValidationException($this->translation->translate('module.form.validation.required'));
                }

                break;
            case 'checked':
                if (!(bool)trim($data)) {
                    throw new ValidationException($this->translation->translate('module.form.validation.checked'));
                }

                break;
            case 'length':
                if (is_array($customData) && (array_key_exists('min', $customData) || array_key_exists('max', $customData))) {
                    $min = $customData['min'] ?? null;
                    $max = $customData['max'] ?? null;

                    if ($min && strlen($data) < $min) {
                        $validation = str_replace('{length}', $min, $this->translation->translate('module.form.validation.length_min'));

                        throw new ValidationException($this->replaceInflections($validation));
                    }

                    if ($max && strlen($data ?? '') > $max) {
                        $validation = str_replace('{length}', $max, $this->translation->translate('module.form.validation.length_max'));

                        throw new ValidationException($this->replaceInflections($validation));
                    }
                }

                break;
            case 'isEmail':
                if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                    throw new ValidationException($this->translation->translate('module.form.validation.isEmail'));
                }

                break;
            case 'isInteger':
                if (!filter_var($data, FILTER_VALIDATE_INT)) {
                    throw new ValidationException($this->translation->translate('module.form.validation.isInteger'));
                }

                break;
            case 'isDecimal':
                $data = str_replace(',', '.', $data);

                if (!is_numeric($data)) {
                    throw new ValidationException($this->translation->translate('module.form.validation.invalidInt'));
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
                    $validation = str_replace('{decimal}', $maxPlaces, $this->translation->translate('module.form.validation.decimalMax'));

                    throw new ValidationException($this->replaceInflections($validation));
                }

                break;
            case 'enum':
                $names = array_column($customData::cases(), 'name');

                if (!in_array($data, $names)) {
                    throw new ValidationException($this->translation->translate('module.form.validation.invalidEnum'));
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