<?php

namespace Phntm\Lib\Pages\Manage;

use Phntm\Lib\Pages\AbstractManagePage;

abstract class Singleton extends AbstractManagePage
{
    public function __invoke(): void
    {
        $this->getEntityInstance();

        $this->renderWith([
            'entity' => $this->entityClass,
            'form' => $this->formSchema(),
        ]);
    }
}
