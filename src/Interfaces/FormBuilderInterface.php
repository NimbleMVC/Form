<?php

namespace Nimblephp\form\Interfaces;

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

}