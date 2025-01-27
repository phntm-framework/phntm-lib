<?php

namespace Phntm\Lib\Commands;

use Phntm\Lib\Db\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminInit extends Command
{
    private string $name = 'admin:init';

    protected function configure()
    {
        $this
            ->setDescription('Initialize the admin user')
            ->setHelp('This command initializes the admin user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = Db::getEntityManager();

        $admin = new \Phntm\Lib\Db\Entity\Admin(
            'admin',
            password_hash('admin', PASSWORD_DEFAULT),
            bin2hex(random_bytes(32))
        );

        $entityManager->persist($admin);

        $entityManager->flush();

        return Command::SUCCESS;
    }
}
