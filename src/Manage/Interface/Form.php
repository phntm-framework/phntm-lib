<?php

namespace Phntm\Lib\Manage\Interface;

use Phntm\Lib\Model;

class Form
{
    protected string $modelClass;

    public function __construct(public Model $model)
    {
        $this->modelClass = get_class($model);
    }

    public function getInputs(): array
    {
        $attributes = $this->model->getAttributes();
    }
}
