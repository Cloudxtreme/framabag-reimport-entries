<?php

namespace Wallabag\Reimport\Console;

use Wallabag\Reimport\Reimport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CleanCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("clean")
            ->setDescription("Clean entries")
            ->addArgument("username", InputArgument::REQUIRED, "Username")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $reimport = new Reimport($username);
        $reimport->run();
    }
}