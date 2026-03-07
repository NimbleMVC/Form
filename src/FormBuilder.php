<?php

namespace NimblePHP\Form;

use NimblePHP\Form\Enum\MethodEnum;
use NimblePHP\Form\Exceptions\ValidationException;
use NimblePHP\Form\Interfaces\FormBuilderInterface;
use NimblePHP\Framework\DependencyInjector;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Framework\Exception\NotFoundException;
use NimblePHP\Framework\Interfaces\ControllerInterface;
use NimblePHP\Framework\Kernel;
use NimblePHP\Framework\Libs\Classes;
use NimblePHP\Framework\Request;
use NimblePHP\Framework\Traits\LoadModelTrait;
use NimblePHP\Framework\Traits\LogTrait;

abstract class FormBuilder implements FormBuilderInterface
{

    use LoadModelTrait;
    use LogTrait;

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
     * Controller instance
     * @var ?ControllerInterface
     */
    public ?ControllerInterface $controller = null;

    /**
     * Request instance
     * @var Request
     */
    public Request $request;

    /**
     * Input data
     * @var array
     */
    public array $data = [];

    /**
     * Post data
     * @var array
     */
    public array $postData = [];

    /**
     * Create default data
     */
    public function __construct(?ControllerInterface $controller = null)
    {
        $this->controller = $controller;
        $this->form = $this->getFormInstance();
        $this->form->setId(md5(static::class));
        $this->request = Kernel::$serviceContainer->get('kernel.request');
        $this->postData = $this->request->getAllPost();
    }

    /**
     * Init method
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * Render form
     * @param string $name
     * @param ControllerInterface|null $controller
     * @param array $data
     * @return string
     * @throws NotFoundException
     * @throws NimbleException
     */
    public static function generate(string $name, ?ControllerInterface $controller = null, array $data = []): string
    {
        $class = Classes::findClassName($name, '\App\Form\\');

        if (!class_exists($class)) {
            throw new NotFoundException('Not found form ' . $name);
        }

        /** @var FormBuilder $formBuilder */
        $formBuilder = new $class($controller);
        DependencyInjector::inject($formBuilder);
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
     * Get form instance
     * @return Form
     */
    public function getFormInstance(): object
    {
        return $this->form ?? new Form($this->action, $this->method);
    }

}