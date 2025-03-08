<?php

namespace NimblePHP\Form;

use Exception;
use NimblePHP\Form\Enum\MethodEnum;
use NimblePHP\Form\Exceptions\ValidationException;
use NimblePHP\Form\Interfaces\FormBuilderInterface;
use NimblePHP\Framework\DependencyInjector;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Framework\Exception\NotFoundException;
use NimblePHP\Framework\Interfaces\ControllerInterface;
use NimblePHP\Framework\Log;
use NimblePHP\Framework\Request;
use NimblePHP\Framework\Traits\LoadModelTrait;

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
     * Controller instance
     * @var ?ControllerInterface
     */
    public ?ControllerInterface $controller = null;

    /**
     * Request instance
     * @var Request
     */
    protected Request $request;

    /**
     * Input data
     * @var array
     */
    protected array $data = [];

    /**
     * Post data
     * @var array
     */
    protected array $postData = [];

    /**
     * Create default data
     */
    public function __construct(?ControllerInterface $controller = null)
    {
        $this->controller = $controller;
        $this->form = new Form($this->action, $this->method);
        $this->request = new Request();
        $this->postData = $this->request->getAllPost();
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
        $class = '\App\Form\\' . str_replace('/', '\\', $name);

        if (!class_exists($class)) {
            throw new NotFoundException('Not found form ' . $name);
        }

        /** @var \NimblePHP\Form\FormBuilder $formBuilder */
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
     * Create logs
     * @param string $message
     * @param string $level
     * @param array $content
     * @return bool
     * @throws Exception
     * @action disabled
     */
    public function log(string $message, string $level = 'INFO', array $content = []): bool
    {
        return Log::log($message, $level, $content);
    }

}