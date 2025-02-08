<?php

namespace Nimblephp\form\Field;

use Krzysztofzylka\HtmlGenerator\HtmlGenerator;

class FieldTextarea extends Field
{

    /**
     * Get content
     * @return string
     */
    public function getContent(): string
    {
        return $this->getData() ?? $this->attributes['value'] ?? '';
    }

}