<?php

namespace Nimblephp\form;

use Nimblephp\debugbar\Debugbar;

/**
 * Form generator (bootstrap)
 */
class FormBootstrap extends Form
{

    /**
     * Add linebreak
     * @var bool
     */
    protected bool $addLinebreak = false;

    /**
     * Group active
     * @var bool
     */
    protected bool $group = false;

    /**
     * Col
     * @var int|null
     */
    protected ?int $col = null;

    /**
     * Start group
     * @param int $col
     * @return self
     */
    public function startGroup(int $col = 6): self
    {
        $this->fields[] = [
            'type' => 'group-start',
            'col' => $col
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
     * Create select
     * @param string $name
     * @param array $options
     * @param null|string|array $selectedKey
     * @param string|null $title
     * @param array $attributes
     * @return $this
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
     * Render field
     * @param array $field
     * @return string
     */
    protected function renderField(array $field): string
    {
        if ($field['type'] === 'group-start') {
            $this->col = $field['col'];
            return '<div class="row">';
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
            $tag = '';
            $tagContent = '';
            $attributes = $field['attributes'];

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