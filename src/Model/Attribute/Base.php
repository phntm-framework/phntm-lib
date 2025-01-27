<?php

namespace Phntm\Lib\Model\Attribute;

use Phntm\Lib\Model;

abstract class Base
{
    public string $columnType;

    public Model $model;

    public string $columnName;

    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    public function setColumnName(string $columnName): void
    {
        if (isset($this->model::$colPrefix)) {
            $columnName = $this->model::$colPrefix . '_' . $columnName;
        }
        $this->columnName = $columnName;
    }

    public function getColumnDefinition(): string
    {
        return "`{$this->getColumnName()}` {$this->getColumnType()}" . ($this->required ? ' NULL' : '');
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function getColumnType(): string
    {
        return $this->columnType;
    }

    public function getDbValue(): mixed
    {
        return $this->model->{$this->getColumnName()};
    }

    public function fromDbValue(mixed $value): mixed
    {
        return $value;
    }

    abstract public function getOptions(): array;
}
