<?php

namespace Wallabag\Reimport\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CleanAllCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('clean-all')
            ->setDescription('Clean entries for all users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime();

        $logger = new Logger('clean-all');
        $logger->pushHandler(new StreamHandler('logs/all-'.$date->format('Ymd-Hi').'.log', Logger::DEBUG));

        $logger->addInfo('Starting reimport');

        $command = $this->getApplication()->find('clean');

        $locator = new FileLocator(array(__DIR__.'/../Resources'));
        $config = $locator->locate('config.yml');
        $configValues = Yaml::parse(file_get_contents($config));

        $finder = new Finder();
        $finder->depth('== 0');
        $finder->files()->in($configValues['reimport']['clean']['folder']);

        foreach ($finder->directories() as $directory) {
            $arguments = array(
                    'command' => 'clean',
                    'username' => $directory->getRelativePathname(),
            );

            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);
        }
    }
}
