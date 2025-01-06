<?php

namespace Nimblephp\form;

use Exception;
use Nimblephp\form\Enum\MethodEnum;
use Nimblephp\form\Exceptions\ValidationException;
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
     * Input data
     * @var array
     */
    protected array $data = [];

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
     * @param array $data
     * @return string
     * @throws NotFoundException
     */
    public static function generate(string $name, ?ControllerInterface $controller = null, array $data = []): string
    {
        $class = '\src\Form\\' . $name;

        if (!class_exists($class)) {
            throw new NotFoundException('Not found form ' . $name);
        }

        /** @var FormBuilder $formBuilder */
        $formBuilder = new $class($controller);
        $formBuilder->data = $data;
        $formBuilder->init();
        $formBuilder->create();
        $formBuilder->form->validation($formBuilder->validation());

        if ($formBuilder->form->onSubmit()) {
            $formBuilder->onSubmit();
        }

        return $formBuilder->form->render();
    }

    /**
     * Add error
     * @param string $name
     * @param string $error
     * @return void
     */
    public function addError(string $name, string $error): void
    {
        $this->form->validation(
            [
                $name => [
                    function () use ($error) {
                        throw new ValidationException($error);
                    }
                ]
            ]
        );
    }

    /**
     * Magic get method
     * @param string $name
     * @return mixed
     * @throws Exception
     * @action disabled
     */
    public function __get(string $name)
    {
        $loadModel = $this->__getModel($name);

        if (!is_null($loadModel)) {
            return $loadModel;
        }

        $className = $this::class;

        throw new Exception("Undefined property: {$className}::{$name}", 2);
    }

}