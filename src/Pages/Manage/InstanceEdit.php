<?php

namespace Phntm\Lib\Pages\Manage;

use Phntm\Lib\Http\Redirect;
use Phntm\Lib\Pages\AbstractManagePage;
use Symfony\Component\HttpFoundation\Request;

abstract class InstanceEdit extends AbstractManagePage
{
    protected ?string $backLink = null;

    public function __invoke(Request $request): void
    {
        if ($request->isMethod('POST')) {
            if (!$this->entity) {
                $this->entity = new $this->entityClass;
            }

            foreach ($this->entity->getAttributes() as $col => $attribute) {
                $this->entity->{$col} = $attribute->fromRequest($request);
            }

            $this->entity->save();

            // redirect to this page
            throw new Redirect($request->getRequestUri(), 302);
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
