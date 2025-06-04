<?php

namespace NimblePHP\Form\Traits;

use NimblePHP\Form\Form;

trait Validation
{

    /**
     * Global validations
     */
    public static array $VALIDATIONS = [];

    /**
     * Validation errors
     * @var array
     */
    protected array $validationErrors = [];

    /**
     * Validate form
     * @param array $validations
     * @return bool
     */
    public function validation(array $validations = []): bool
    {
        if (!$this->prepareData()) {
            return false;
        }

        if ((!is_null($this->id) && isset($this->data['formId']) && $this->getId() !== htmlspecialchars($this->data['formId']))
            || (!is_null($this->id) && !isset($this->data['formId']))
        ) {
            return false;
        }

        $validation = new \NimblePHP\Form\Validation($validations, $this->getData());
        $this->validationErrors = array_merge($this->validationErrors, $validation->run());
        Form::$VALIDATIONS = $this->validationErrors;

        return true;
    }

    /**
     * Add validation
     * @param string $fieldName
     * @param string $validationText
     * @return void
     */
    public function addValidation(string $fieldName, string $validationText): void
    {
        $this->validationErrors[$fieldName] = $validationText;
        Form::$VALIDATIONS[$fieldName] = $this->validationErrors;
    }

}