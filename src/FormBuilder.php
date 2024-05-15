<?php

namespace Nimblephp\form;

use Nimblephp\form\Enum\MethodEnum;
use Nimblephp\framework\Exception\NotFoundException;
use Nimblephp\framework\Interfaces\ControllerInterface;
use Nimblephp\framework\Kernel;

class FormBuilder
{

    /**
     * Form instance
     * @var Form
     */
    public Form $form;

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
     * Layout
     * @var string|null
     */
    protected ?string $layout = null;

    /**
     * Controller instance
     * @var ControllerInterface
     */
    protected ?ControllerInterface $controller = null;

    /**
     * Render form
     * @param string $name
     * @param ControllerInterface|null $controller
     * @return string
     */
    public static function render(string $name, ?ControllerInterface $controller = null): string
    {
        $class = '\src\Form\\' . $name;

        if (!class_exists($class)) {
            throw new NotFoundException('Not found form ' . $name);
        }

        /** @var FormBuilder $formBuilder */
        $formBuilder = new $class($controller);
        $formBuilder->init();
        $formBuilder->create();
        $formBuilder->form->validation($formBuilder->validation());

        if ($formBuilder->form->onSubmit()) {
            $formBuilder->onSubmit();
        }

        return $formBuilder->form->render();
    }

    /**
     * Create default data
     */
    public function __construct(?ControllerInterface $controller = null)
    {
        $this->controller = $controller;

        if ($this->layout === 'bootstrap') {
            $this->form = new FormBootstrap($this->action, $this->method);
        } else {
            $this->form = new Form($this->action, $this->method);
        }
    }

    /**
     * Initialize
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * Create form
     * @return void
     */
    public function create(): void
    {
    }

    /**
     * Create validation
     * @return array
     */
    public function validation(): array
    {
        return [];
    }

    /**
     * On submit action
     * @return void
     */
    public function onSubmit(): void
    {
    }

}