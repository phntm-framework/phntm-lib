<?php

namespace Phntm\Lib\Pages\Manage;

use Phntm\Lib\Db\Db;
use Phntm\Lib\Http\Redirect;
use Phntm\Lib\Model;
use Phntm\Lib\Pages\AbstractManagePage;
use Symfony\Component\HttpFoundation\Request;

abstract class Listing extends AbstractManagePage
{
    protected int $perPage = 10;

    protected string $editLink;

    protected string|false|null $render_view = ROOT . PHNTM . 'views/manage-table.twig';

    protected function getEntityInstance(): ?Model
    {
        return null;
    }
    protected function handleEntityNotFound(): void
    {
        return;
    }

    public function __invoke(Request $request): void
    {
        if (
            $request->getMethod() === 'POST'
            && $request->request->has('selected')
            && $request->request->has('action')
        ) {
            $post = $request->request->getIterator()->getArrayCopy();
            $db = Db::getConnection();
            $qb = $db->createQueryBuilder();

            $first = array_shift($post['selected']);
            $qb->delete($this->entityClass::getTableName())
                ->where('id = ?')
                ->setParameter(0, $first);

            foreach ($post['selected'] as $i => $id) {
                $qb->orWhere('id = ?')
                    ->setParameter($i + 1, $id);
            }

            $result = $db->executeQuery($qb->getSQL(), $qb->getParameters());

            throw new Redirect($request->getRequestUri(), 302);
        }

        $this->renderWith([
            'columns' => $this->entityClass::getTableColumns(),
            'entities' => $this->entityClass::all(),
            'editLink' => $this->editLink,
        ]);
    }
    protected function resolveEntityIdentifier(): null|int|array
    {
        return [];
    }

    abstract protected function resolveEditUrl(Model $entity): string;
}
