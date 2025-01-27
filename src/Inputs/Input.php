<?php

namespace Phntm\Lib\Inputs;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Input
{
    public function __construct(
        public string $input = 'input',
        public ?string $type = 'text',
        public ?string $label = null,
        public string $placeholder = '',
        public bool $required = false,
        public bool $readonly = false,
        public bool $disabled = false,
        public ?int $minlength = null,
        public ?int $maxlength = null,
        public mixed $default = null,
    ) {
    }

    public function getAttributes(\ReflectionProperty $property): array
    {
        $values = [
            'element' => $this->input,
            'label' => $this->label ?? ucfirst($property->getName()),
            'attributes' => [
                'name' => $property->getName(),
                'id' => $property->getName(),
                'type' => $this->type,
            ],
        ];

        if ($this->placeholder) {
            $values['attributes']['placeholder'] = $this->placeholder;
        }

        if ($this->required) {
            $values['attributes']['required'] = $this->required;
        }

        if ($this->readonly) {
            $values['attributes']['readonly'] = $this->readonly;
        }

        if ($this->disabled) {
            $values['attributes']['disabled'] = $this->disabled;
        }

        if ($this->minlength) {
            $values['attributes']['minlength'] = $this->minlength;
        }

        if ($this->maxlength) {
            $values['attributes']['maxlength'] = $this->maxlength;
        }

        return $values;
    }
}
