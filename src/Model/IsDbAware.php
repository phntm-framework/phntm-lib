<?php

namespace Phntm\Lib\Model;

use Phntm\Lib\Db\Db;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

trait IsDbAware
{
    public static function getTableName(): string
    {
        return static::$table ?? strtolower((new \ReflectionClass(static::class))->getShortName());
    }

    public static function tableExists(): bool
    {
        $sm = Db::getSchemaManager();
        
        return $sm->tablesExist([static::getTableName()]);
    }

    public static function createTable(): void
    {
        $sm = Db::getSchemaManager();

        $table = static::getCurrentSchema();

        $sm->createTable($table);
    }

    public static function getTableColumns(): array
    {
        $sm = Db::getSchemaManager();
        $columns = $sm->listTableColumns(static::getTableName());

        return array_map(function ($column) {
            return $column->getName();
        }, $columns);
    }

    public static function getCurrentSchema(): Table
    {
        $schema = new Schema();
        $table = $schema->createTable(static::getTableName());

        foreach ((new static())->getAttributes() as $attribute) {
            $col = $table->addColumn(
                $attribute->getColumnName(),
                $attribute->getColumnType(),
                [...$attribute->getOptions(), 'notnull' => false]
            );
            if ($attribute->isUnique()) {
                $table->addUniqueConstraint([$attribute->getColumnName()]);
            }
        }

        $table->setPrimaryKey(['id'], 'id');

        return $table;
    }

    public static function getExistingSchema(): Table
    {
        $sm = Db::getSchemaManager();

        return $sm->introspectTable(static::getTableName());
    }
}
