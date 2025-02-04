<?php

namespace Phntm\Lib\Pages\Manage;

use Phntm\Lib\Pages\AbstractManagePage;
use Symfony\Component\HttpFoundation\Request;

class Singleton extends AbstractManagePage
{
    public function __invoke(Request $request): void
    {
        $this->getEntityInstance();

        $this->renderWith([
            'entity' => $this->entityClass,
            'form' => $this->formSchema(),
        ]);
    }
}
