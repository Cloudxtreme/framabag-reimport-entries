<?php

namespace Wallabag\Reimport\Console;

use Wallabag\Reimport\Reimport;
use Wallabag\Reimport\Service\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class CleanAllCommand extends Command
{
    private $database;

    protected function configure()
    {
        $this
            ->setName('clean-all')
            ->setDescription('Clean entries for all users')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locator = new FileLocator(array(__DIR__.'/../Resources'));
        $config = $locator->locate('config.yml');
        $configValues = Yaml::parse(file_get_contents($config));

        $db = new Database($configValues['reimport']['clean']['folder']);

        $query = $db->getPdo()->prepare("SELECT * from `entries` WHERE `content` = '' OR `content` = '[unable to retrieve full-text content]';");
        $query->execute();
        $results = $query->fetchAll();

        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, count($results));

        $reimport = new Reimport($input->getArgument('username'));

        foreach ($results as $result) {
            $run = $reimport->run($result['url']);

            $sql = 'UPDATE entries SET content=? WHERE id=?';
            $params = array($run->getBody(), $result['id']);
            $query = $db->getPdo()->prepare($sql);
            $query->execute($params);

            $progress->advance();
        }

        $progress->finish();
    }
}
