<?php

namespace Wallabag\Reimport\Entity;

class Url
{
    private $url;

    function __construct($url)
    {
        $this->url = base64_decode($url);
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function isCorrect() {
        return filter_var($this->url, FILTER_VALIDATE_URL) !== FALSE;
    }
}