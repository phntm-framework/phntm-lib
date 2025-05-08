<?php

namespace Phntm\Lib\Pages\Manage;

use Phntm\Lib\Http\Redirect;
use Phntm\Lib\Pages\AbstractManagePage;

abstract class InstanceEdit extends AbstractManagePage
{
    protected ?string $backLink = null;

    public function __invoke(): void
    {
        if ('POST' === $this->getRequest()->getMethod()) {
            if (!$this->entity) {
                $this->entity = new $this->entityClass;
            }

            foreach ($this->entity->getAttributes() as $col => $attribute) {
                $this->entity->{$col} = $attribute->fromRequest($this->getRequest());
            }

            $this->entity->save();

            // redirect to this page
            throw new Redirect($this->getRequest()->getUri(), 302);
        }

        $this->renderWith([
            'entity' => $this->entityClass,
            'form' => $this->formSchema(),
            'backLink' => $this->backLink,
        ]);
    }

    protected function handleEntityNotFound(): void
    {
        $this->entity = new $this->entityClass;
    }
}
