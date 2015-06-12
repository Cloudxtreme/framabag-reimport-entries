<?php

namespace Wallabag\Reimport;

use Wallabag\Reimport\Service\Extractor;

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

    public function run($url)
    {
        $content = Extractor::extract($url);
        if ($content->getBody() <> '' && $content->getBody() <> '[unable to retrieve full-text content]') {
            // Update database
        }
        else {
            // store this URL for debug
        }
    }
}