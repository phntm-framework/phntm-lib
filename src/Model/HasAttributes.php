<?php

namespace Phntm\Lib\Model;

trait HasAttributes
{
    protected array $attributes = [];

    public function getAttributes(): array
    {
        if (!empty($this->attributes)) {
            return $this->attributes;
        }

        $attributes = [];
        $reflection = new \ReflectionClass(static::class);
        while ($reflection) {
            $this->sourcePropertyAttributes($reflection, $attributes);
            $reflection = $reflection->getParentClass();
        }

        $this->attributes = $attributes;

        return $attributes;
    }

    protected function sourcePropertyAttributes(\ReflectionClass $reflection, array &$attributes): void
    {
        foreach ($reflection->getProperties() as $property) {
            $attribute = $property->getAttributes(Attribute\Base::class, \ReflectionAttribute::IS_INSTANCEOF);
            if (empty($attribute)) {
                continue;
            }

            $attribute = $attribute[0]->newInstance();

            $attribute->setModel($this);
            $attribute->setColumnName($property->getName());

            $attributes[$attribute->getColumnName()] = $attribute;
        }
    }

    public function getAttributeNames(): array
    {
        return array_values(array_map(function ($attribute) {
            return $attribute->getColumnName();
        }, $this->getAttributes()));
    }

    public function getAttribute(string $name): ?Attribute\Base
    {
        return $this->getAttributes()[$name] ?? null;
    }
}
