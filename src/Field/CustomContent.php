<?php

namespace Nimblephp\form\Field;

use Nimblephp\form\Interfaces\FieldInterface;

class CustomContent implements FieldInterface
{

    /**
     * Custom content
     * @var string
     */
    protected string $content;

    /**
     * Constructor
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function render(): string
    {
        return $this->content;
    }

}