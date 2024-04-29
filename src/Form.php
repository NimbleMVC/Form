<?php

namespace Nimblephp\form;

use Krzysztofzylka\Arrays\Arrays;
use Nimblephp\form\Enum\MethodEnum;
use Nimblephp\form\Exceptions\ValidationException;
use Nimblephp\framework\Request;

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
     * Form id
     * @var string|null
     */
    protected ?string $id = null;

    /**
     * Request instance
     * @var Request
     */
    protected Request $request;

    /**
     * Form input data
     * @var array
     */
    protected array $data = [];

    /**
     * Add linebreak
     * @var bool
     */
    protected bool $addLinebreak = true;

    /**
     * Validation errors
     * @var array
     */
    protected array $validationErrors = [];

    /**
     * Initialize form
     * @param string|null $action
     * @param MethodEnum $method
     */
    public function __construct(?string $action = null, MethodEnum $method = MethodEnum::POST)
    {
        if (is_null($action)) {
            $action = $_SERVER['REQUEST_URI'];
        }

        $this->action = $action;
        $this->method = $method;
        $this->request = new Request();
    }

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

        $validation = new Validation($validations, $this->getData());
        $this->validationErrors = $validation->run();

        return true;
    }

    /**
     * Add field
     * @param string $type
     * @param string|null $name
     * @param ?string $title
     * @param array $attributes
     * @param array $options
     * @return $this
     */
    public function addField(string $type, ?string $name, ?string $title, array $attributes = [], array $options = []): self
    {
        $data = $this->getDataByKey($name);

        if (!is_null($data)) {
            $attributes['value'] = $data;

            if ($type === 'checkbox') {
                $attributes['checked'] = 'checked';
            }
        }

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
     * Add input
     * @param string $name
     * @param string|null $title
     * @param array $attributes
     * @return $this
     */
    public function addFloatInput(string $name, ?string $title = null, array $attributes = []): self
    {
        return $this->addField(
            type: 'number',
            name: $name,
            title: $title,
            attributes: array_merge(['step' => '0.01'], $attributes)
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
        $data = $this->getDataByKey($name);

        if (!is_null($data)) {
            $selectedKey = $data;
        }

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
     * Add input hidden
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addInputHidden(string $name, string $value): self
    {
        $data = $this->getDataByKey($name);

        if (!is_null($data)) {
            $value = $data;
        }

        return $this->addField(
            type: 'hidden',
            name: $name,
            title: null,
            attributes: [
                'value' => $value
            ]
        );
    }

    /**
     * Render html form
     * @return string
     */
    public function render(): string
    {
        if ($this->id) {
            $this->addInputHidden('formId', $this->getId());
        }

        $formAttributes = [
            'action' => $this->action,
            'method' => $this->method->value,
        ];
        $formContent = '';

        foreach ($this->fields as $field) {
            $formContent .= $this->renderField($field) . ($this->addLinebreak ? '<br />' : '');
        }

        return '<form' . $this->generateAttributes($formAttributes) . '>' . $formContent . '</form>';
    }

    /**
     * Add submit button
     * @param string $value
     * @param ?array $attributes
     * @return self
     */
    public function addSubmitButton(string $value, ?array $attributes = []): self
    {
        return $this->addField(
            type: 'submit',
            name: null,
            title: null,
            attributes: [
                'value' => $value
            ] + $attributes,
        );
    }

    /**
     * Get form id
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set form id
     * @param string|null $id
     * @return void
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * On submit form
     * @return bool
     */
    public function onSubmit(): bool
    {
        if (!empty($this->validationErrors)) {
            return false;
        }

        if (!$this->prepareData()) {
            return false;
        }

        if (empty($this->data)) {
            return false;
        }

        if ((!is_null($this->id) && isset($this->data['formId']) && $this->getId() !== htmlspecialchars($this->data['formId']))
            || (!is_null($this->id) && !isset($this->data['formId']))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Get data (htmlspecialchars))
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
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
                'name' => $this->generateName($field['name'] ?? ''),
                'id' => $this->generateId($field['name'] ?? ''),
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
                    $html .= '<label for="' . $attributes['id'] . '">' . $field['title'] . '</label>' . ($this->addLinebreak ? '<br />' : '');
                }

                $tag = 'select';

                foreach ($field['options']['options'] as $key => $name) {
                    $selected = (string)$field['options']['selectedKey'] === (string)$key;
                    $tagContent .= '<option value="' . $key . '"' . ($selected ? 'selected' : '') . '>' . $name . '</option>';
                }
                break;
        }

        if ($field['title']) {
            $html .= '<label for="' . $attributes['id'] . '">' . $field['title'] . '</label>' . ($this->addLinebreak ? '<br />' : '');
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

    /**
     * Set data
     * @param array $data
     * @return void
     */
    protected function setData(array $data): void
    {
        $this->data = Arrays::htmlSpecialChars($data);
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

        $data = $this->getData();

        if (empty($data)) {
            if ($this->method === MethodEnum::POST) {
                $data = Arrays::htmlSpecialChars($_POST);
            } elseif ($this->method === MethodEnum::GET) {
                $data = Arrays::htmlSpecialChars($_GET);
            }
        }

        return @eval('return $data["' . str_replace('/', '"]["', $name) . '"];');
    }

    /**
     * Prepare data
     * @return bool
     */
    protected function prepareData(): bool
    {
        if ($this->method === MethodEnum::GET) {
            if (!isset($_GET)) {
                return false;
            }

            $this->setData($_GET);
        } elseif ($this->method === MethodEnum::POST) {
            if (!isset($_POST)) {
                return false;
            }

            $this->setData($_POST);
        }

        return true;
    }

}
