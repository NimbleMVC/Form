<?php

namespace Nimblephp\form\Field;

use Krzysztofzylka\HtmlGenerator\HtmlGenerator;

class FieldInputHidden extends Field
{

    /**
     * Render field
     * @return string
     */
    public function render(): string
    {
        $attributes = [
            ...$this->getAttributes(),
            'type' => 'hidden',
        ];

        if (!is_null($this->getData())) {
            $attributes['value'] = $this->getData();
        }

        return HtmlGenerator::createTag(
            'input',
            '',
            null,
            $attributes
        );
    }

}