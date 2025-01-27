<?php

namespace Phntm\Lib\Commands;

use Phntm\Lib\Config;
use Phntm\Lib\Db\Db;
use Phntm\Lib\Model\Admin;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function password_hash;

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
        $admin = new Admin();
        $admin->username = Config::get()['auth']['admin']['username'];
        $admin->password = password_hash(
            Config::get()['auth']['admin']['password'],
            PASSWORD_DEFAULT
        );
        $admin->save();

        return Command::SUCCESS;
    }
}
