<?php

namespace Nimblephp\form\Traits;

use Krzysztofzylka\Arrays\Arrays;
use Nimblephp\form\Enum\MethodEnum;

trait Helpers
{

    /**
     * Generate html tag attributes
     * @param array $attributes
     * @return string
     */
    protected function generateAttributes(array $attributes): string
    {
        $attributesHtml = '';

        foreach ($attributes as $key => $value) {
            if (is_null($value) || is_array($value)) {
                continue;
            }

            if (str_contains($value, '"')) {
                $attributesHtml .= ' ' . $key . '=\'' . $value . '\'';
            } else {
                $attributesHtml .= ' ' . $key . '="' . $value . '"';
            }
        }


        return $attributesHtml;
    }

    /**
     * Generate field name
     * @param string $name
     * @param string $prefix
     * @return string
     */
    protected function generateName(string $name, string $prefix = ''): string
    {
        $core = str_starts_with($name, '/');

        if ($core) {
            $name = substr($name, 1);
            $prefix .= '/';
        }

        $explode = explode('/', $name, 2);

        return $prefix . $explode[0] . (isset($explode[1]) ? ('[' . implode('][', explode('/', $explode[1])) . ']') : '');
    }

    /**
     * Generate field id
     * @param string $name
     * @return string
     */
    protected function generateId(string $name): string
    {
        $return = '';
        $explode = explode('/', $name);

        foreach ($explode as $value) {
            $value = strtolower($value);
            $return .= empty($return) ? $value : ucfirst($value);
        }

        return $return;
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

}