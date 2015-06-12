<?php

namespace Wallabag\Reimport;

use Wallabag\Reimport\Service\Extractor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class Reimport
{
    /**
     * @var string
     */
    private $username;

    public function __construct($username)
    {
        $this->username = $username;
    }

    private function executeQuery($handle, $sql, $params)
    {
        try
        {
            $query = $handle->prepare($sql);
            $query->execute($params);
            return $query;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    public function run()
    {
        $configDirectories = array(__DIR__.'/Resources');

        $locator = new FileLocator($configDirectories);
        $config = $locator->locate('config.yml');
        
        $configValues = Yaml::parse(file_get_contents($config));

        $folder = $configValues['reimport']['clean']['folder'];

        $db_path = 'sqlite:'.$folder.'/poche.sqlite';
        $handle = new \PDO($db_path);
        $handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $emptyEntries = 0;
        $fixedEntries = 0;
        $notFixedEntries = array();

        $sql = "select * from entries WHERE content = '' OR content = '[unable to retrieve full-text content]' LIMIT 30;";
        $query = $this->executeQuery($handle, $sql, array());
        $results = $query->fetchAll();
        $emptyEntries += count($results);
        foreach ($results as $result) {
            $content = Extractor::extract($result['url']);
            if ($content->getBody() <> '' && $content->getBody() <> '[unable to retrieve full-text content]') {
                $fixedEntries++;
            }
            else {
                $notFixedEntries[] = $result['url'];
            }
        }

        var_dump($fixedEntries);
        var_dump($notFixedEntries);
    }
}