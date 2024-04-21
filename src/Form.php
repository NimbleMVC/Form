<?php

namespace Nimblephp\form;

use Nimblephp\form\Enum\MethodEnum;

/**
 * Form generator
 */
class Form
{

    /**
     * Fields
     * @var array
     */
    protected array $fields = [];

    /**
     * Form method
     * @var MethodEnum
     */
    protected MethodEnum $method = MethodEnum::POST;

    /**
     * Form action
     * @var ?string
     */
    protected ?string $action = null;

    /**
     * Initialize form
     * @param string|null $action
     * @param MethodEnum $method
     */
    public function __construct(?string $action = null, MethodEnum $method = MethodEnum::POST)
    {
        $this->action = $action;
        $this->method = $method;
    }

    /**
     * Add field
     * @param string $type
     * @param string $name
     * @param ?string $title
     * @param array $attributes
     * @return $this
     */
    public function addField(string $type, string $name, ?string $title, array $attributes = [], array $options = []): self
    {
        $this->fields[] = [
            'type' => $type,
            'name' => $name,
            'title' => $title,
            'attributes' => $attributes,
            'options' => $options
        ];

        return $this;
    }

    /**
     * Add input
     * @param string $name
     * @param string|null $title
     * @param array $attributes
     * @return $this
     */
    public function addInput(string $name, ?string $title = null, array $attributes = []): self
    {
        return $this->addField(
            type: 'input',
            name: $name,
            title: $title,
            attributes: $attributes
        );
    }

    /**
     * Add textarea
     * @param string $name
     * @param string|null $title
     * @param array $attributes
     * @return $this
     */
    public function addTextarea(string $name, ?string $title=null, array $attributes = []): self
    {
        return $this->addField(
            type: 'textarea',
            name: $name,
            title: $title,
            attributes: $attributes
        );
    }

    /**
     * Create select
     * @param string $name
     * @param array $options
     * @param string|null $selectedKey
     * @param string|null $title
     * @param array $attributes
     * @return $this
     */
    public function addSelect(
        string $name,
        array $options,
        ?string $selectedKey = null,
        ?string $title = null,
        array $attributes = []
    ): self
    {
        return $this->addField(
            type: 'select',
            name: $name,
            title: $title,
            attributes: $attributes,
            options: [
                'options' => $options,
                'selectedKey' => $selectedKey
            ]
        );
    }

    /**
     * Render html form
     * @return string
     */
    public function render(): string
    {
        $formAttributes = [
            'action' => $this->action,
            'method' => $this->method->value,
        ];
        $formContent = '';

        foreach ($this->fields as $field) {
            $formContent .= $this->renderField($field) . '<br />';
        }

        return '<form' . $this->generateAttributes($formAttributes) . '>' . $formContent . '</form>';
    }

    /**
     * Render field
     * @param array $field
     * @return string
     */
    protected function renderField(array $field): string
    {
        $html = '';
        $tagContent = '';
        $tag = 'input';
        $attributes = [
            'name' => $this->generateName($field['name']),
            'id' => $this->generateId($field['name']),
            'type' => $field['type']
        ] + $field['attributes'];

        switch ($field['type']) {
            case 'checkbox':
                return '<' . $tag . $this->generateAttributes($attributes) . ' />' . $field['title'];
            case 'textarea':
                $tag = 'textarea';

                if ($field['attributes']['value']) {
                    $tagContent = $field['attributes']['value'];
                    unset($field['attributes']['value']);
                }
                break;
            case 'select':
                if ($field['title']) {
                    $html .= '<label for="' . $attributes['id'] . '">' . $field['title'] . '</label><br />';
                }

                $tag = 'select';

                foreach ($field['options']['options'] as $key => $name) {
                    $selected = (string)$field['options']['selectedKey'] === (string)$key;
                    $tagContent .= '<option value="' . $key . '"' . ($selected ? 'selected' : '') . '>' . $name . '</option>';
                }
                break;
        }

        if ($field['title']) {
            $html .= '<label for="' . $attributes['id'] . '">' . $field['title'] . '</label><br />';
        }

        return $html . '<' . $tag . $this->generateAttributes($attributes) . '>' . $tagContent . '</' . $tag . '>';
    }

    /**
     * Generate html tag attributes
     * @param array $attributes
     * @return string
     */
    protected function generateAttributes(array $attributes): string
    {
        $attributesHtml = '';

        foreach ($attributes as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            if (str_contains($value, '"')) {
                $attributesHtml .= ' ' . $key . '=\'' . $value . '\'';
            } else {
                $attributesHtml .= ' ' . $key . '="' . $value . '"';
            }
        }


        return $attributesHtml;
    }

    /**
     * Generate field name
     * @param string $name
     * @param string $prefix
     * @return string
     */
    protected function generateName(string $name, string $prefix = ''): string
    {
        $core = str_starts_with($name, '/');

        if ($core) {
            $name = substr($name, 1);
            $prefix .= '/';
        }

        $explode = explode('/', $name, 2);

        return $prefix . $explode[0] . (isset($explode[1]) ? ('[' . implode('][', explode('/', $explode[1])) . ']') : '');
    }

    /**
     * Generate field id
     * @param string $name
     * @return string
     */
    protected function generateId(string $name): string
    {
        $return = '';
        $explode = explode('/', $name);

        foreach ($explode as $value) {
            $value = strtolower($value);
            $return .= empty($return) ? $value : ucfirst($value);
        }

        return $return;
    }

}