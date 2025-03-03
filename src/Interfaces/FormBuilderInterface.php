<?php

namespace NimblePHP\Form\Interfaces;

interface FormBuilderInterface
{

    /**
     * On submit action
     * @return void
     */
    public function onSubmit(): void;

    /**
     * Initialize
     * @return void
     */
    public function init(): void;

    /**
     * Create form
     * @return void
     */
    public function create(): void;

    /**
     * Create validation
     * @return array
     */
    public function validation(): array;

    /**
     * Add error
     * @param string $name
     * @param string $error
     * @return void
     */
    public function addError(string $name, string $error): void;

}