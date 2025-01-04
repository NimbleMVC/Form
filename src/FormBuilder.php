<?php

namespace Nimblephp\form;

use Nimblephp\form\Enum\MethodEnum;
use Nimblephp\form\Interfaces\FormBuilderInterface;
use Nimblephp\framework\Exception\NotFoundException;
use Nimblephp\framework\Interfaces\ControllerInterface;
use Nimblephp\framework\Traits\LoadModelTrait;

abstract class FormBuilder implements FormBuilderInterface
{

    use LoadModelTrait;

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
     * @var ?ControllerInterface
     */
    protected ?ControllerInterface $controller = null;

    /**
     * Create default data
     */
    public function __construct(?ControllerInterface $controller = null)
    {
        $this->controller = $controller;
        $this->form = new Form($this->action, $this->method);
    }

    /**
     * Render form
     * @param string $name
     * @param ControllerInterface|null $controller
     * @return string
     * @throws NotFoundException
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

}