<?php

namespace Nimblephp\form;

use Nimblephp\form\Enum\MethodEnum;
use Nimblephp\form\Traits\Field;
use Nimblephp\form\Traits\Helpers;
use Nimblephp\framework\Request;

class Form
{

    use Helpers;
    use Field;
    use \Nimblephp\form\Traits\Validation;

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
     * Add form node
     * @var bool
     */
    protected bool $addFormNode = true;

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
     * Set add form node
     * @param bool $addFormNode
     * @return $this
     */
    public function setAddFormNode(bool $addFormNode): self
    {
        $this->addFormNode = $addFormNode;

        return $this;
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

        return $content . '<script>$("#' . $this->getId() . '").ajaxform();</script>';
    }

}