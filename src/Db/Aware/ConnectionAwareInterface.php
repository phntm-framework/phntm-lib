<?php

namespace Phntm\Lib\Db\Aware;

interface ConnectionAwareInterface
{
    public function setConnection(\Doctrine\DBAL\Connection $connection): ConnectionAwareInterface;
    public function getConnection(): \Doctrine\DBAL\Connection;
}
