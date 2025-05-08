<?php

namespace Phntm\Lib\Model\Attribute;

use Phntm\Lib\Model;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base for all model data attributes
 *
 * handles the conversion of data between the DB, forms, and model
 *
 */
abstract class Base
{
    use Traits\HasHooks;

    public string $columnType;

    public string $inputTemplate;

    public Model $model;

    public string $columnName;


    // field attributes
    public bool $hidden = false;
    public bool $required = false;
    public ?string $label = null;
    public bool $unique = false;

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


    // Value retrieval

    /**
     * Get the value in its format for the database
     *
     * @return mixed
     */
    public function getDbValue(): mixed
    {
        return $this->model->{$this->getColumnName()};
    }

    /**
     * Get the value in its format for the form
     *
     * @return mixed
     */
    public function getFormValue(): mixed
    {
        if (!isset($this->model->{$this->getColumnName()})) {
            return null;
        }
        return $this->model->{$this->getColumnName()};
    }

    // Value conversion

    /**
     * Convert the value from the database format to the model format
     *
     * @param mixed $value
     * @return mixed
     */
    public function fromDbValue(mixed $value): mixed
    {
        return $value;
    }

    /**
     * Convert the value from the form format to the model format
     *
     * @param mixed $value
     * @return mixed
     */
    public function fromFormValue(mixed $value): mixed
    {
        return $value;
    }

    public function getOldValue(): mixed
    {
        return $this->model->getOldValue($this->getColumnName());
    }

    public function fromRequest(ServerRequestInterface $request): mixed
    {
        $post = $request->getParsedBody();
        // if the request does not have the column, return the current value
        if (!isset($post[$this->getColumnName()])) {
            return $this->model->{$this->getColumnName()};
        }

        return $this->fromFormValue($post[$this->getColumnName()]);
    }

    public function getOptions(): array
    {
        return [
            ...$this->getBaseOptions(),
        ];
    }


    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getFormAttributes(): array
    {
        $values = [
            'element' => $this->inputTemplate,
            'label' => $this->label ?? ucfirst($this->getColumnName()),
            'value' => $this->getFormValue(),
            'attributes' => [
                'name' => $this->getColumnName(),
                'id' => $this->model->getTableName() . '.' . $this->getColumnName(),
            ],
        ];

        return $values;
    }

    public function getBaseOptions(): array
    {
        return [
        ];
    } 

    public function isUnique(): bool
    {
        return $this->unique;
    }

}
