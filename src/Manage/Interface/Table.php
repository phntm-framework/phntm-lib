<?php

namespace Phntm\Lib\Manage\Interface;

use Doctrine\DBAL\Query\QueryBuilder;
use Phntm\Lib\Model;

class Table
{
    protected string $modelClass;

    public function __construct(public Model $model, protected QueryBuilder $queryForRows)
    {
        $this->modelClass = get_class($model);
    }

    public function getRows(): array
    {
        $rows = $this->model->all();
        $attributes = $this->model->getAttributes();
        $rows = array_map(function ($row) use ($attributes) {
            $rowArray = [];
            foreach ($attributes as $attribute) {
                $rowArray[$attribute] = $row->$attribute;
            }
            return $rowArray;
        }, $rows);

        return $rows;
    }
}
