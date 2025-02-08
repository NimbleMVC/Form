<?php

namespace Nimblephp\form;

use Krzysztofzylka\Arrays\Arrays;
use Krzysztofzylka\HtmlGenerator\HtmlGenerator;
use Nimblephp\form\Enum\MethodEnum;
use Nimblephp\form\Field\Field;
use Nimblephp\form\Field\FieldCheckbox;
use Nimblephp\form\Field\FieldInputHidden;
use Nimblephp\framework\Request;

class Form
{

    /**
     * Form action
     * @var string
     */
    protected string $action;

    /**
     * Form method
     * @var MethodEnum
     */
    protected MethodEnum $method;

    /**
     * Request instance
     * @var Request
     */
    protected Request $request;

    /**
     * Form id
     * @var string|null
     */
    protected ?string $id = null;

    /**
     * Form input data
     * @var array
     */
    protected array $data = [];

    /**
     * Fields
     * @var array
     */
    protected array $fields = [];

    /**
     * Initialize form
     * @param string|null $action
     * @param MethodEnum $method
     */
    public function __construct(?string $action = null, MethodEnum $method = MethodEnum::POST)
    {
        if (is_null($action)) {
            $action = $_SERVER['REQUEST_URI'] ?? '';
        }

        $this->action = $action;
        $this->method = $method;
        $this->request = new Request();
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
     * Get data
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set data
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
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
     * Add field
     * @param string $nodeName
     * @param string|null $name
     * @param string|null $title
     * @param array $attributes
     * @param string|null $class
     * @return self
     */
    public function addField(
        string  $nodeName,
        ?string $name,
        ?string $title,
        array   $attributes = [],
        ?string $class = null
    ): self
    {
        $field = new Field($nodeName);
        $field->setName($name);
        $field->setTitle($title);
        $field->setAttributes($attributes);
        $field->setClass($class);
        $field->setData($this->getDataByKey($name));
        $this->fields[] = $field;

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
        return $this->addField('input', $name, $title, $attributes);
    }

    /**
     * Add input hidden
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addInputHidden(string $name, string $value): self
    {
        $field = new FieldInputHidden('input');
        $field->setName($name);
        $field->setAttributes(['value' => $value]);
        $field->setData($this->getDataByKey($name));
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Add checkbox
     * @param string $name
     * @param string|null $title
     * @param array $attributes
     * @return $this
     */
    public function addCheckbox(string $name, ?string $title = null, array $attributes = []): self
    {
        $field = new FieldCheckbox('input');
        $field->setName($name);
        $field->setTitle($title);
        $field->setAttributes($attributes);
        $field->setData($this->getDataByKey($name));
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Render html form
     * @param bool $addFormNode
     * @return string
     */
    public function render(bool $addFormNode = true): string
    {
        $html = '';

        foreach ($this->fields as $field) {
            $html .= $field->render($addFormNode);
        }

        if ($addFormNode) {
            $html = (string)HtmlGenerator::createTag(
                'form',
                $html,
                'ajax-form',
                [
                    'action' => $this->action,
                    'method' => $this->method->value,
                    ...($this->getId() ? ['id' => $this->getId()] : [])
                ]
            );
        }

        return $html;
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

            $this->setData($this->request->getAllPost());
        }

        return true;
    }

    /**
     * Get data by key
     * @param string|null $name
     * @return mixed
     */
    protected function getDataByKey(?string $name): mixed
    {
        if (empty($name)) {
            return null;
        }

        $data = $this->getData();

        if (empty($data)) {
            if ($this->method === MethodEnum::POST) {
                $data = $this->request->getAllPost();
            } elseif ($this->method === MethodEnum::GET) {
                $data = $this->request->getAllQuery();
            }
        }

        $explodeName = explode('/', $name);

        foreach ($explodeName as $key => $value) {
            if (empty(trim($value))) {
                unset($explodeName[$key]);
            }
        }

        return Arrays::getNestedValue($data, $explodeName);
    }

}