<?php

namespace Phntm\Lib;

use Phntm\Lib\Model\Attribute as Col;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Phntm\Lib\Db\Db;
use Phntm\Lib\Model\IsDbAware;
use Phntm\Lib\Model\HasAttributes;

abstract class Model
{
    use IsDbAware;
    use HasAttributes;

    protected static string $table;

    protected static ?string $colPrefix = null;
    
    #[Col\Id(
        hidden: true,
        unsigned: true,
    )]
    public int $id = 0;

    protected bool $isPersisted = false;

    protected static Connection $db;

    public function __construct(
    ) {
    }

    protected function load(array $data): static
    {
        foreach ($data as $key => $value) {
            $attribute = $this->getAttribute($key);
            $this->{$key} = $attribute->fromDbValue($value);
        }

        $this->isPersisted = true;
        return $this;
    }

    /**
     * Persists the models current state to the database
     *
     * @return static
     * @throws \Exception
     */
    public function save(): static
    {
        if ($this->isPersisted) {
            $this->update();
        } else {
            $this->create();
        }

        return $this;
    }

    public function create(): static
    {
        $values = [];

        $db = static::db();
        $qb = $db->createQueryBuilder();

        $qb
            ->insert(static::getTableName())
        ;
        foreach ($this->getAttributes() as $col => $attribute) {
            if ($attribute->getColumnName() === 'id') {
                continue;
            }

            $qb->setValue($attribute->getColumnName(), '?');
            if (!isset($this->{$col})) {
                $values[] = null;
                continue;
            }
            $values[] = $attribute->getDbValue();
        }
        $qb->setParameters($values);

        $result = $db->executeQuery($qb->getSQL(), $values);

        $this->isPersisted = true;

        return $this;
    }

    public function update(): static
    {
        $db = static::db();
        $qb = $db->createQueryBuilder();

        $qb
            ->update(static::getTableName())
            ->where('id = :id')
            ->setParameter('id', $this->id)
        ;
        foreach ($this->getAttributes() as $col => $attribute) {
            if ($attribute->getColumnName() === 'id') {
                continue;
            }
            $qb->set($attribute->getColumnName(), ':'. $attribute->getColumnName());
            $qb->setParameter($attribute->getColumnName(), $attribute->getDbValue());
        }
        $db->executeQuery($qb->getSQL(), $qb->getParameters());

        return $this;
    }

    public static function find(int $id): ?static
    {
        $instance = new static();

        $db = static::db();

        $qb = $db->createQueryBuilder();

        $qb
            ->select('id', ...$instance->getAttributeNames())
            ->setMaxResults(1)
            ->from(static::getTableName())
            ->where("id = {$id}")
            //->setParameter('id', $id)
        ;

        $query = $qb->getSQL();

        $result = $db->executeQuery($query);
        if ($result->rowCount() === 0) {
            return null;
        }

        $instance->load($result->fetchAssociative());

        return $instance;
    }

    public static function query(): QueryBuilder
    {
        $instance = new static();
        $qb = static::db()->createQueryBuilder();
        $qb->select('id', ...$instance->getAttributeNames())
            ->from(static::getTableName())
        ;
    }

    public static function where(array|string $colOrQuery, mixed $value = null): static|array|null
    {
        $instance = new static();
        $qb = static::db()->createQueryBuilder();
        $qb->select('id', ...$instance->getAttributeNames())
            ->from(static::getTableName())
        ;

        if (is_array($colOrQuery)) {
            $i = 0;
            foreach ($colOrQuery as $col => $value) {
                $qb->andWhere("{$col} = ?");
                $qb->setParameter($i, $value);
            }
        } else {
            $qb->andWhere("{$colOrQuery} = ?");
            $qb->setParameter(0, $value);
        }

        $query = $qb->getSQL();

        $result = static::db()->executeQuery($query, $qb->getParameters());

        if ($result->rowCount() === 0) {
            return null;
        }

        if ($result->rowCount() > 1) {
            $models = [];
            while ($row = $result->fetchAssociative()) {
                $instance = new static();
                $instance->load($row);
                $models[] = $instance;
            }

            return $models;
        }

        $instance->load($result->fetchAssociative());
        return $instance;
    }

    public static function fromQuery(QueryBuilder $qb): array
    {
        $values = $qb->getParameters();

        $query = $qb->getSQL();

        Db::getConnection()->executeQuery($query, $values);
        while ($row = $result->fetchAssociative()) {
            $instance = new static();
            $instance->load($row);
            $models[] = $instance;
        }

        return $models;
    }

    public static function all(): array
    {
        $instance = new static();
        $qb = static::db()->createQueryBuilder();
        $qb->select('id', ...$instance->getAttributeNames())
            ->from(static::getTableName())
        ;

        $query = $qb->getSQL();

        $result = static::db()->executeQuery($query);

        $models = [];
        while ($row = $result->fetchAssociative()) {
            $instance = new static();
            $instance->load($row);
            $models[] = $instance;
        }

        return $models;
    }

    public static function db(): Connection
    {
        return Db::getConnection();
    }

    public static function getTableColumns(): array
    {
        return [];
    }

    public function injectToUrl(string $url): string
    {
        // get parts in {curly braces}
        preg_match_all('/\{([^}]+)\}/', $url, $matches);
        foreach ($matches[1] as $match) {
            $url = str_replace('{' . $match . '}', $this->$match, $url);
        }

        return $url;
    }

    public function setupHooks(): void
    {
    }
}
