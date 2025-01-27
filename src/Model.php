<?php

namespace Phntm\Lib;

use Phntm\Lib\Model\Attribute as Col;
use Doctrine\DBAL\Connection;
use Phntm\Lib\Db\Db;
use Phntm\Lib\Model\IsFindable;
use Phntm\Lib\Model\IsDbAware;
use Phntm\Lib\Model\HasAttributes;

abstract class Model
{
    use IsDbAware;
    use IsFindable;
    use HasAttributes;

    protected static string $table;

    protected static ?string $colPrefix = null;
    
    #[Col\Integer(
        hidden: true,
        unsigned: true,
    )]
    private int $id = 0;

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
            //$this->update();
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
            $qb->setValue($attribute->getColumnName(), '?');
            $values[] = $attribute->getDbValue();
        }
        $qb->setParameters($values);

        $db->executeQuery($qb->getSQL(), $values);

        $this->isPersisted = true;

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

    public static function all(): array
    {
        return [];
    }

    public static function db(): Connection
    {
        return Db::getConnection();
    }
}
