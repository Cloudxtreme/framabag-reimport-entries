<?php

namespace Wallabag\Reimport\Console;

use Wallabag\Reimport\Reimport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class CleanCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('clean')
            ->setDescription('Clean entries')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locator = new FileLocator(array(__DIR__.'/../Resources'));
        $config = $locator->locate('config.yml');
        $configValues = Yaml::parse(file_get_contents($config));

        $handle = new \PDO('sqlite:'.$configValues['reimport']['clean']['folder'].'/poche.sqlite');
        $handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $query = $handle->prepare("SELECT * from `entries` WHERE `content` = '' OR `content` = '[unable to retrieve full-text content]' LIMIT 20;");
        $query->execute();
        $results = $query->fetchAll();

        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, count($results));

        $reimport = new Reimport($input->getArgument('username'));

        foreach ($results as $result) {
            $reimport->run($result['url']);
            $progress->advance();
        }

        $progress->finish();
    }
}
