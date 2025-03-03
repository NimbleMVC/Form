<?php

namespace NimblePHP\Form\Traits;

trait Field
{

    use Helpers;

    /**
     * Fields
     * @var array
     */
    private array $fields = [];

    /**
     * Col attributes
     * @var array
     */
    protected array $colAttributes = [];

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

            if ($type === 'checkbox') {
                if (!empty($data)) {
                    $attributes['checked'] = 'checked';
                }
            } else {
                $attributes['value'] = $data;
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
     * Add checkbox
     * @param string $name
     * @param string|null $title
     * @param array $attributes
     * @return self
     */
    public function addCheckbox(string $name, ?string $title = null, array $attributes = []): self
    {
        return $this->addField(
            type: 'checkbox',
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
     * Add custom raw data
     * @param string $content
     * @return Field|\NimblePHP\Form\Form
     */
    public function addRawCustomData(string $content): self
    {
        $this->fields[] = [
            'type' => 'customRawData',
            'content' => $content
        ];

        return $this;
    }

    /**
     * Start group
     * @param int $col
     * @param array $attributes
     * @return self
     */
    public function startGroup(int $col = 6, array $rowAttributes = [], array $colAttributes = []): self
    {
        $this->fields[] = [
            'type' => 'group-start',
            'col' => $col,
            'attributes' => ['row' => $rowAttributes, 'col' => $colAttributes]
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
     * Render field
     * @param array $field
     * @return string
     */
    protected function renderField(array $field): string
    {
        if ($field['type'] === 'customRawData') {
            return $field['content'];
        }

        if ($field['type'] === 'group-start') {
            $this->colAttributes = $field['attributes']['col'];
            $this->colAttributes['class'] =  'col-' . $field['col'] . ' ' . trim(($this->colAttributes['class'] ?? ''));
            $field['attributes']['row']['class'] = trim(($field['attributes']['row']['class'] ?? '') . ' row');

            return '<div ' . $this->generateAttributes($field['attributes']['row']) . '>';
        } elseif ($field['type'] === 'group-stop') {
            $this->colAttributes = [];

            return '</div>';
        } elseif ($field['type'] === 'title') {
            return '<legend>' . $field['title'] . '</legend>';
        }

        $divAttributes = $this->colAttributes;
        $divAttributes['class'] = 'mb-3 ' . ($divAttributes['class'] ?? '');

        $html = '<div ' . $this->generateAttributes($divAttributes) . '>';// . ' ' . ($this->col > 0 ? ('col-' . $this->col) : '') . '">';
        $tagContent = '';
        $tag = 'input';
        $additional = '';
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
                unset($attributes['name']);
                $attributes['class'] = str_replace('form-control', '', $attributes['class']);
                $attributes['class'] = ($attributes['class'] ?? '') . ' form-check-input';
                $attributes['onchange'] = '$(\'#_\' + $(this).attr(\'id\')).val($(this).is(\':checked\') ? 1 : 0)';

                $additional .= $this->renderField([
                    'type' => 'hidden',
                    'name' => $this->generateName($field['name'] ?? ''),
                    'title' => null,
                    'attributes' => [
                        'id' => '_' . $this->generateId($field['name'] ?? ''),
                        'value' => $this->getDataByKey($field['name']) ? 1 : 0,
                    ]
                ]);
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

        return $html . $additional . '</div>';
    }

}