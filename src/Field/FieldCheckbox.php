<?php

namespace Nimblephp\form\Field;

use Krzysztofzylka\HtmlGenerator\HtmlGenerator;

class FieldCheckbox extends Field
{

    /**
     * Render field
     * @return string
     */
    public function render(): string
    {
        $attributes = [
            ...$this->getAttributes(),
            'type' => 'checkbox',
            'class' => 'form-check-input',
            'onchange' => '$(\'#_\' + $(this).attr(\'id\')).val($(this).is(\':checked\') ? 1 : 0)'
        ];

        if (!is_null($this->getData()) && (bool)$this->getData()) {
            $attributes['checked'] = 'checked';
        }

        unset($attributes['name']);

        if ($this->validationError) {
            $attributes['class'] = $attributes['class'] . ' border-danger';
        }

        return HtmlGenerator::createTag(
            'div',
            HtmlGenerator::createTag(
                $this->getNodeName(),
                '',
                null,
                $attributes
            )
            . $this->generateLabelTag()
            . $this->hiddenInput()
            . $this->generateErrorTag(),
            trim('mb-3 form-check ' . $this->getClass())
        );
    }

    /**
     * Generate label tag
     * @return string
     */
    public function generateLabelTag(): string
    {
        if (!$this->getTitle()) {
            return '';
        }

        return HtmlGenerator::createTag(
            'label',
            $this->getTitle(),
            'form-check-label',
            ['for' => $this->getId()]
        );
    }

    /**
     * Hidden input
     * @return string
     */
    public function hiddenInput(): string
    {
        $attributes = [
            'id' => '_' . $this->getId(),
            'name' => $this->getName(),
            'value' => $this->getData() ?? 0,
            'type' => 'hidden'
        ];

        return HtmlGenerator::createTag(
            'input',
            '',
            null,
            $attributes
        );
    }

}