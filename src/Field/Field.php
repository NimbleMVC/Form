<?php

namespace Nimblephp\form\Field;

use Krzysztofzylka\HtmlGenerator\HtmlGenerator;

class Field
{

    /**
     * Node name
     * @var string
     */
    protected string $nodeName;

    /**
     * Field name
     * @var string|null
     */
    protected ?string $name;

    /**
     * Field title
     * @var string|null
     */
    protected ?string $title;

    /**
     * Field attributes
     * @var array
     */
    protected array $attributes = [];

    /**
     * Field class
     * @var string|null
     */
    protected ?string $class = null;

    /**
     * Data
     * @var string|null
     */
    protected ?string $data = null;

    /**
     * Validation error
     * @var string|null
     */
    public ?string $validationError = null;

    /**
     * Construct field
     * @param $nodeName
     */
    public function __construct($nodeName)
    {
        $this->nodeName = $nodeName;
    }

    /**
     * Get node name
     * @return string
     */
    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    /**
     * Get field id
     * @return string
     */
    public function getId(): string
    {
        $return = '';
        $explode = preg_split('/[\/_]/', $this->name);

        foreach ($explode as $value) {
            $value = strtolower($value);
            $return .= empty($return) ? $value : ucfirst($value);
        }

        return $return;
    }

    /**
     * Field name
     * @return ?string
     */
    public function getName(): ?string
    {
        $core = str_starts_with($this->name, '/');

        if ($core) {
            $name = substr($this->name, 1);
            $prefix = '/';
        }

        $explode = explode('/', $name ?? $this->name, 2);

        return ($prefix ?? '') . $explode[0] . (isset($explode[1]) ? ('[' . implode('][', explode('/', $explode[1])) . ']') : '');
    }

    /**
     * Set field name
     * @param ?string $name
     * @return void
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get field title
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set field title
     * @param string|null $title
     * @return void
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get attributes
     * @return array
     */
    public function getAttributes(): array
    {
        return [
            'name' => $this->getName(),
            'id' => $this->getId(),
            ...$this->attributes
        ];
    }

    /**
     * Set attributes
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Get class
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * Set class
     * @param string|null $class
     * @return void
     */
    public function setClass(?string $class): void
    {
        $this->class = $class;
    }

    /**
     * Get content
     * @return string
     */
    public function getContent(): string
    {
        return '';
    }

    /**
     * Render field
     * @return string
     */
    public function render(): string
    {
        $attributes = ['class' => 'form-control', ...$this->getAttributes()];

        if (!is_null($this->getData())) {
            $attributes['value'] = $this->getData();
        }

        if ($this->validationError) {
            $attributes['class'] = $attributes['class'] . ' border-danger';
        }

        return HtmlGenerator::createTag(
            'div',
            $this->generateLabelTag()
            . HtmlGenerator::createTag(
                $this->getNodeName(),
                $this->getContent(),
                null,
                $attributes
            )
            . $this->generateErrorTag(),
            trim('mb-3 ' . $this->getClass())
        );
    }

    /**
     * Generate error tag
     * @return string
     */
    public function generateErrorTag(): string
    {
        if (!$this->validationError) {
            return '';
        }

        return HtmlGenerator::createTag(
            'div',
            $this->validationError,
            'validation text-danger'
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
            'form-label',
            ['for' => $this->getId()]
        );
    }

    /**
     * Get data
     * @return string|null
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * Set data
     * @param string|null $data
     * @return void
     */
    public function setData(?string $data): void
    {
        $this->data = $data;
    }

}