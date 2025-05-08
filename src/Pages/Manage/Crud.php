<?php

namespace Phntm\Lib\Pages\Manage;

use Doctrine\DBAL\Driver\Connection;
use Phntm\Lib\Db\Db;
use Phntm\Lib\Http\Redirect;
use Phntm\Lib\Infra\Routing\Attributes\Action;
use Phntm\Lib\Model;
use Phntm\Lib\Pages\AbstractManagePage;
use Symfony\Component\HttpFoundation\Request;
use Phntm\Lib\Pages\Traits\HasActions;
use function in_array;

abstract class Crud extends AbstractManagePage
{
    use HasActions;

    #[Action('/')]
    public function index(): void
    {
        $this->render_view = ROOT . PHNTM . 'views/manage-table.twig';

        $db = $this->getContainer()->get(Connection::class);
        $qb = $db->createQueryBuilder();
        $qb->select('*')
            ->from($this->entityClass::getTableName())
            ->setMaxResults(10)
        ;

        $result = $db->executeQuery($qb->getSQL());

        $this->renderWith([
            'entity' => $this->entityClass,
            'table' => $this->entityClass::all(),
            'entities' => $result->fetchAllAssociative(),
            'links' => $this->getLinks(),
        ]);
    }

    #[Action('/create')]
    public function create(): void
    {
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $this->entity = new $this->entityClass;
            foreach ($request->getParsedBody() as $col => $value) {
                $attr = $this->entity->getAttribute($col);
                $this->entity->{$col} = $attr->fromFormValue($value);
            }

            $this->entity->save();

            throw new Redirect('/manage/examples/all', 302);
        }

        $this->entity = new $this->entityClass;

        $this->renderWith([
            'entity' => $this->entityClass,
            'form' => $this->formSchema(),
        ]);
    }

    #[Action('/edit/{identifier}')]
    public function edit(): void
    {
        if ('POST' === $this->getRequest()->getMethod()) {
            $this->getEntityInstance();

        }

        $this->renderWith([
            'entity' => $this->entityClass,
            'form' => $this->formSchema(),
        ]);
    }

    #[Action('/delete/{identifier}')]
    public function delete(): void
    {
        $db = Db::getConnection();
        $qb = $db->createQueryBuilder();
        $qb->delete('simple_pages')
            ->where('id = ?')
            ->setParameter(0, $this->id);

        $params = $qb->getParameters();


        $result = $db->executeQuery($qb->getSQL(), $params);

        throw new Redirect('/manage/examples/all', 302);
    }


    protected function getEntityInstance(): Model|null
    {
        $db = Db::getConnection();
        $qb = $db->createQueryBuilder();
        $qb->select('*')
            ->from($this->entityClass::getTableName());

        $result = $db
            ->executeQuery($qb->getSQL(), $qb->getParameters())
            ->fetchAssociative();

        $this->entityClass::where($this->entityResolutionIdentifier, $result[$this->entityResolutionIdentifier]);

        $entity = new $this->entityClass;

        $entity->load($result);

        dd($result, $entity);

        return $entity ? $entity : null;
    }

    protected function handleEntityNotFound(): mixed
    {
        if ($this->matchedAction === 'edit') {
            throw new Redirect('/manage/examples/create', 302);
        }
        $this->entity = new $this->entityClass;
    }

    public function getViewVariables(): array
    {
        return [
            ...parent::getViewVariables(),
            'links' => $this->getLinks(),
        ];
    }

    protected function getLinks(): array
    {
        return [
            'create' => $this->resolveBaseRoute()['path'] . '/create',
            'index' => $this->resolveBaseRoute()['path'],
        ];
    }
}
