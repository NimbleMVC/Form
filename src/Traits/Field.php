<?php

namespace Nimblephp\form\Traits;

trait Field
{

    /**
     * Fields
     * @var array
     */
    private array $fields = [];

    /**
     * Col
     * @var int|null
     */
    protected ?int $col = null;

    /**
     * Add field
     * @param string $type
     * @param string|null $name
     * @param ?string $title
     * @param array $attributes
     * @param array $options
     * @param string|null $class
     * @return self
     */
    public function addField(
        string $type,
        ?string $name,
        ?string $title,
        array $attributes = [],
        array $options = [],
        ?string $class = null
    ): self
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
            'options' => $options,
            'class' => $class
        ];

        return $this;
    }
    /**
     * Add input
     * @param string $name
     * @param string|null $title
     * @param array $attributes
     * @return self
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
     * @return self
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
     * @return self
     */
    public function addTextarea(string $name, ?string $title = null, array $attributes = []): self
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
     * @param null|string|array $selectedKey
     * @param string|null $title
     * @param array $attributes
     * @return self
     */
    public function addSelect(
        string $name,
        array $options,
        null|string|array $selectedKey = null,
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
            ],
            class: 'form-select'
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
     * Start group
     * @param int $col
     * @param array $attributes
     * @return self
     */
    public function startGroup(int $col = 6, array $attributes = []): self
    {
        $this->fields[] = [
            'type' => 'group-start',
            'col' => $col,
            'attributes' => $attributes
        ];

        return $this;
    }

    /**
     * Stop group
     * @return self
     */
    public function stopGroup(): self
    {
        $this->fields[] = [
            'type' => 'group-stop'
        ];

        return $this;
    }

    /**
     * Add title
     * @param string $title
     * @return self
     */
    public function title(string $title): self
    {
        $this->fields[] = [
            'type' => 'title',
            'title' => $title
        ];

        return $this;
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

        $formContent = '';
        $formAttributes = [
            'action' => $this->action,
            'method' => $this->method->value,
            'id' => $this->getId() ?? false,
            'class' => 'ajax-form'
        ];

        foreach ($this->fields as $field) {
            $formContent .= $this->renderField($field);
        }

        ob_start();

        if ($this->addFormNode) {
            echo '<form' . $this->generateAttributes($formAttributes) . '>' . $formContent . '</form>';
        } else {
            echo $formContent;
        }

        $content = ob_get_clean();

        if ($this->request->isAjax() && $this->request->getQuery('form', false) === $this->getId()) {
            ob_flush();
            die($content);
        }

        return $content;
    }

    /**
     * Render field
     * @param array $field
     * @return string
     */
    protected function renderField(array $field): string
    {
        if ($field['type'] === 'group-start') {
            $this->col = $field['col'];
            $field['attributes']['class'] = trim(($field['attributes']['class'] ?? '') . ' row');
            return '<div ' . $this->generateAttributes($field['attributes']) . '>';
        } elseif ($field['type'] === 'group-stop') {
            $this->col = null;
            return '</div>';
        } elseif ($field['type'] === 'title') {
            return '<legend>' . $field['title'] . '</legend>';
        }

        $html = '<div class="mb-3 ' . ($this->col > 0 ? ('col-' . $this->col) : '') . '">';
        $tagContent = '';
        $tag = 'input';
        $attributes = $field['attributes']
            + [
                'name' => $this->generateName($field['name'] ?? ''),
                'id' => $this->generateId($field['name'] ?? ''),
                'type' => $field['type'],
                'class' => $field['class'] ?? 'form-control'
            ];

        if (!empty($this->getData()) && array_key_exists($field['name'], $this->validationErrors)) {
            $attributes['class'] .= ' border-danger';
        }

        switch ($field['type']) {
            case 'submit':
                $attributes['class'] = ($attributes['class'] ?? '') . ' btn btn-primary';
                break;
            case 'checkbox':
                $attributes['class'] = str_replace('form-control', '', $attributes['class']);
                $attributes['class'] = ($attributes['class'] ?? '') . ' form-check-input';
                break;
            case 'textarea':
                $tag = 'textarea';

                if (isset($field['attributes']['value']) && $field['attributes']['value']) {
                    $tagContent = $field['attributes']['value'];
                    unset($attributes['value']);
                }
                break;
            case 'select':
                $tag = 'select';

                foreach ($field['options']['options'] as $key => $name) {
                    if (!is_null($field['options']['selectedKey'])) {
                        if (is_array($field['options']['selectedKey'])) {
                            $selected = in_array((string)$key, $field['options']['selectedKey']);
                        } else {
                            $selected = (string)$field['options']['selectedKey'] === (string)$key;
                        }
                    } else {
                        $selected = false;
                    }

                    $value = $key ? 'value="' . $key . '" ' : '';

                    $tagContent .= '<option ' . $value . ($selected ? 'selected' : '') . '>' . $name . '</option>';
                }
                break;
        }

        if ($field['title'] && $field['type'] !== 'checkbox' && $field['type'] !== 'span') {
            $html .= '<label for="' . $attributes['id'] . '" class="form-label">' . $field['title'] . '</label><br />';
        }

        if ($field['type'] !== 'span') {
            $html = $html . '<' . $tag . $this->generateAttributes($attributes) . '>' . $tagContent . '</' . $tag . '>';
        }

        if ($field['type'] === 'checkbox') {
            $html .= '<label for="' . $attributes['id'] . '" class="form-check-label ms-2">' . $field['title'] . '</label><br />';
        }

        if ($field['type'] === 'span') {
            $tagContent = '';

            if (!empty($field['attributes']['class'])) {
                $tagContent = 'class="' . $field['attributes']['class'] . '"';
            }

            $html .= '<span ' . $tagContent . '>' . $field['title'] . '</span>';
        }

        if (!empty($this->getData()) && array_key_exists($field['name'], $this->validationErrors)) {
            $html .= '<div class="validation text-danger">' . $this->validationErrors[$field['name']] . '</div>';
        }

        return $html . '</div>';
    }

}