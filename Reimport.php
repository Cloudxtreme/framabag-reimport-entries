<?php

namespace Wallabag\Reimport;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Graby\Graby;

class Reimport
{
    /**
     * @var string
     */
    private $username;

    private $logger;

    public function __construct($username)
    {
        $this->username = $username;

        $date = new \DateTime();
        $this->logger = new Logger('clean-'.$username);
        $this->logger->pushHandler(new StreamHandler('logs/users/'.$username.'-'.$date->format('Ymd-Hi').'.log', Logger::DEBUG));

        $this->logger->addInfo('Starting cleaning');
    }

    public function run($url)
    {
        $graby = new Graby();
        $result = $graby->fetchContent($url);

        $debugParams = array('url' => $url);
        if ($result['status'] != '404' && $result['html'] != '' && $result['html'] != '[unable to retrieve full-text content]') {
            // Update database
            $this->logger->addInfo('URL updated with success', $debugParams);

            return $result;
        } else {
            if ($result['html'] == '') {
                $this->logger->addWarning('URL empty', $debugParams);
            } else {
                $this->logger->addWarning('Unable to retrieve content', $debugParams);
            }

            return false;
        }
    }
}
