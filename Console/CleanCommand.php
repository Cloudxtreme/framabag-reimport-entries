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

class CleanCommand extends Command
{
    private $database;

    protected function configure()
    {
        $this
            ->setName('clean')
            ->setDescription('Clean entries for a single user')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        $locator = new FileLocator(array(__DIR__.'/../Resources'));
        $config = $locator->locate('config.yml');
        $configValues = Yaml::parse(file_get_contents($config));

        $db = new Database($configValues['reimport']['clean']['folder'].'/'.$username.'/db/poche.sqlite');

        $query = $db->getPdo()->prepare("SELECT * from `entries` WHERE `content` = '' OR `content` = '[unable to retrieve full-text content]';");
        $query->execute();
        $results = $query->fetchAll();

        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $progress = $this->getHelperSet()->get('progress');
            $progress->start($output, count($results));
        }

        $reimport = new Reimport($username);

        foreach ($results as $result) {
            $run = $reimport->run($result['url']);

            if ($run !== false) {
                $sql = 'UPDATE entries SET content=?, title=? WHERE id=?';
                $params = array($run['html'], $run['title'], $result['id']);
                $query = $db->getPdo()->prepare($sql);
                $query->execute($params);
            }
            if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $progress->advance();
            }
        }
        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $progress->finish();
        }
    }
}
