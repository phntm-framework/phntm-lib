<?php

declare(strict_types=1);

namespace Phntm\Lib\Db\Aware;

use BadMethodCallException;
use Doctrine\DBAL\Connection;

trait ConnectionAwareTrait
{
    /**
     * @var ?DefinitionContainerInterface
     */
    protected $connection;

    public function setConnection(Connection $connection): ConnectionAwareInterface
    {
        $this->connection = $connection;

        if ($this instanceof ConnectionAwareInterface) {
            return $this;
        }

        throw new BadMethodCallException(sprintf(
            'Attempt to use (%s) while not implementing (%s)',
            ConnectionAwareTrait::class,
            ConnectionAwareInterface::class
        ));
    }

    public function getConnection(): Connection
    {
        if ($this->connection instanceof Connection) {
            return $this->connection;
        }

        throw new BadMethodCallException('No connection has been set.');
    }
}
