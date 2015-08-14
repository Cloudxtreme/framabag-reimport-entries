<?php

namespace Wallabag\Reimport\Service;

use Wallabag\Reimport\Entity\Content;
use Wallabag\Reimport\Entity\Url;

final class Extractor
{
    public static function extract($url)
    {
        $pageContent = self::getPageContent(new Url(base64_encode($url)));
        $title = $pageContent['rss']['channel']['item']['title'] ?: 'Untitled';
        $body = $pageContent['rss']['channel']['item']['description'];

        $purifier = self::getPurifier();
        $title = $purifier->purify($title);
        $body = $purifier->purify($body);

        $content = new Content();
        $content->setTitle($title);
        $content->setBody($body);

        return $content;
    }

    /**
     * Returns new purifier object with actual config
     */
    private static function getPurifier()
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', 'cache');
        $config->set('HTML.SafeIframe', true);
        //allow YouTube, Vimeo and dailymotion videos
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/|www\.dailymotion\.com/embed/video/)%');
        return new \HTMLPurifier($config);
    }

    /**
     * Get the content for a given URL (by a call to FullTextFeed).
     *
     * @param Url $url
     *
     * @return mixed
     */
    private static function getPageContent(Url $url)
    {
        // Saving and clearing context
        $REAL = array();
        foreach ($GLOBALS as $key => $value) {
            if ($key != 'GLOBALS' && $key != '_SESSION' && $key != 'HTTP_SESSION_VARS') {
                $GLOBALS[$key] = array();
                $REAL[$key] = $value;
            }
        }
        // Saving and clearing session
        if (isset($_SESSION)) {
            $REAL_SESSION = array();
            foreach ($_SESSION as $key => $value) {
                $REAL_SESSION[$key] = $value;
                unset($_SESSION[$key]);
            }
        }

        // Running code in different context
        $scope = function () {
            extract(func_get_arg(1));
            $_GET = $_REQUEST = array(
                'url' => $url->getUrl(),
                'max' => 5,
                'links' => 'preserve',
                'exc' => '',
                'format' => 'json',
                'submit' => 'Create Feed',
            );
            ob_start();
            require func_get_arg(0);
            $json = ob_get_contents();
            ob_end_clean();

            return $json;
        };

        // Silence $scope function to avoid
        // issues with FTRSS when error_reporting is to high
        // FTRSS generates PHP warnings which break output
        $json = @$scope(__DIR__.'/../vendor/wallabag/Fivefilters_Libraries/makefulltextfeed.php', array('url' => $url));

        // Clearing and restoring context
        foreach ($GLOBALS as $key => $value) {
            if ($key != 'GLOBALS' && $key != '_SESSION') {
                unset($GLOBALS[$key]);
            }
        }
        foreach ($REAL as $key => $value) {
            $GLOBALS[$key] = $value;
        }

        // Clearing and restoring session
        if (isset($REAL_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                unset($_SESSION[$key]);
            }

            foreach ($REAL_SESSION as $key => $value) {
                $_SESSION[$key] = $value;
            }
        }

        return json_decode($json, true);
    }
}
