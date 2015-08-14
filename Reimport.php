<?php

namespace Wallabag\Reimport;

use Wallabag\Reimport\Service\Extractor;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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
        $this->logger = new Logger('reimport-' . $username);
        $this->logger->pushHandler(new StreamHandler('logs/reimport-'.$username.'.log', Logger::DEBUG));

        $this->logger->addInfo('Starting reimport');
    }

    public function run($url)
    {
        $content = Extractor::extract($url);
        $debugParams = array('url' => $url);
        if ($content->getBody() != '' && $content->getBody() != '[unable to retrieve full-text content]') {
            // Update database
            $this->logger->addInfo('URL updated with success', $debugParams);
            return $content;
        } else {
            if ($content->getBody() == '') {
                $this->logger->addWarning('URL empty', $debugParams);
            } else {
                $this->logger->addWarning('Unable to retrieve content', $debugParams);
            }

            return false;
        }
    }
}
