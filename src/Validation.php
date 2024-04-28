<?php

namespace Nimblephp\form;

use Krzysztofzylka\Arrays\Arrays;
use Nimblephp\form\Enum\MethodEnum;
use Nimblephp\form\Exceptions\ValidationException;

/**
 * Form validation
 */
class Validation
{

    /**
     * Fields
     * @var array
     */
    protected array $fields = [];

    /**
     * POST or GET data
     * @var array
     */
    protected array $data;

    /**
     * Validation errors
     * @var array
     */
    protected array $validationErrors;

    /**
     * Construct
     * @param array $validationList
     * @param array $data
     */
    public function __construct(array $validationList, array $data)
    {
        $this->fields = $validationList;
        $this->data = $data;
    }

    /**
     * Run validation
     * @return array
     */
    public function run(): array
    {
        foreach ($this->fields as $fieldKey => $validationList) {
            foreach ($validationList as $validation) {
                try {
                    $validation($this->getDataByKey($fieldKey));
                } catch (ValidationException $exception) {
                    $this->validationErrors[$fieldKey] = $exception->getMessage();
                }
            }
        }

        return $this->validationErrors;
    }

    /**
     * Get data by key
     * @param string|null $name
     * @return ?string
     */
    protected function getDataByKey(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        $data = $this->data;

        return @eval('return $data["' . str_replace('/', '"]["', $name) . '"];');
    }

}