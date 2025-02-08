<?php

namespace Nimblephp\form\Field;

use Krzysztofzylka\HtmlGenerator\HtmlGenerator;

class FieldSelect extends Field
{

    /**
     * Field options
     * @var array
     */
    protected array $options = [];

    /**
     * Selected key
     * @var string|null
     */
    protected ?string $selectedKey = null;

    /**
     * Get options
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set options
     * @param array $options
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Get selected key
     * @return string|null
     */
    public function getSelectedKey(): ?string
    {
        return $this->selectedKey;
    }

    /**
     * Set selected key
     * @param string|null $selectedKey
     * @return void
     */
    public function setSelectedKey(?string $selectedKey): void
    {
        $this->selectedKey = $selectedKey;
    }

    /**
     * Render field
     * @return string
     */
    public function render(): string
    {
        $attributes = [
            'class' => 'form-select',
            ...$this->getAttributes()
        ];

        return HtmlGenerator::createTag(
            'div',
            $this->generateLabelTag()
            . HtmlGenerator::createTag(
                $this->getNodeName(),
                $this->generateOptions(),
                null,
                $attributes
            )
            . $this->generateErrorTag(),
            trim('mb-3 ' . $this->getClass())
        );
    }

    /**
     * Generate options
     * @return string
     */
    public function generateOptions(): string
    {
        $options = [];

        foreach ($this->getOptions() as $optionKey => $optionValue) {
            $selected = null;

            if (!is_null($this->getData())) {
                $selected = $this->getData();
            } elseif ($this->getSelectedKey()) {
                $selected = $this->getSelectedKey();
            }

            $options[] = HtmlGenerator::createTag(
                'option',
                $optionValue,
                null,
                [
                    'value' => $optionKey,
                    ...(!is_null($selected) && (string)$selected === (string)$optionKey ? ['selected' => 'selected'] : [])
                ]
            );
        }

        return implode('', $options);
    }


}