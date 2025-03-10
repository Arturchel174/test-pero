<?php

namespace common\bitrix24\response\service;

class ResponseService
{
    private array $_actions;
    private array $_data;

    public function addAction($action)
    {
        $this->_actions[] = $action;
    }

    public function addValueOfData($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function getActions(): array
    {
        return $this->_actions;
    }

    public function getData(): array
    {
        return $this->_data;
    }

    public function loadActions()
    {
        $this->addValueOfData('actions', $this->getActions());
    }

    public function getResponse(): array
    {
        return [
            'success' => true,
            'data' => $this->getData(),
        ];
    }
}