<?php

namespace Phntm\Lib\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Phntm\Lib\Db\Aware\ConnectionAwareInterface;
use Phntm\Lib\Db\Aware\ConnectionAwareTrait;
use Phntm\Lib\Model;

class Finder implements ConnectionAwareInterface, ContainerAwareInterface
{
    use ConnectionAwareTrait;
    use ContainerAwareTrait;

    /* SPECIFICITY */

    // @var class-string<\Phntm\Lib\Model>
    protected string $modelType;

    public function forModel(string|Model $modelType): static
    {
        if ($modelType instanceof Model) {
            $modelType = get_class($modelType);
        } else if (!is_string($modelType)) {
            throw new \InvalidArgumentException('Model type must be a string or an instance of Model');
        }

        return $this->getContainer()->get(static::class)
            ->setModelType($modelType);
    }

    public function setModelType(string $modelType): static
    {
        $this->modelType = $modelType;
        return $this;
    }


    public function fromQuery(QueryBuilder $qb): array
    {
        $models = [];

        $result = $this->getConnection()->executeQuery(
            $qb->getSQL(),
            $qb->getParameters(),
        );

        while ($row = $result->fetchAssociative()) {
            $instance = $this->getNewInstance();
            $instance->load($row);
            $models[] = $instance;
        }

        return $models;
    }


    public function where(array|string $colOrQuery, mixed $value = null): array
    {
        if (!isset($this->modelType)) {
            throw new \Exception('Model type not set. Use forModel() to get a finder for a specific model.');
        }

        $instance = $this->getContainer()->get($this->modelType);

        $qb = $this->getConnection()->createQueryBuilder();

        $qb->select('id', ...$instance->getAttributeNames())
            ->from($this->modelType::getTableName())
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

        $result = $this->getConnection()->executeQuery(
            $qb->getSQL(),
            $qb->getParameters(),
        );

        $models = [];
        while ($row = $result->fetchAssociative()) {
            $instance = $this->getNewInstance();
            $instance->load($row);
            $models[] = $instance;
        }

        return $models;
    }

    public function getNewInstance(): Model
    {
        if (!isset($this->modelType)) {
            throw new \Exception('Model type not set. Use forModel() to get a finder for a specific model.');
        }

        return $this->getContainer()->getNew($this->modelType);
    }
}

