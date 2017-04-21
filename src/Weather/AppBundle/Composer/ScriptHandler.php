<?php

namespace Weather\AppBundle\Composer;

use Symfony\Component\Filesystem\Filesystem;
use Composer\Script\Event;

class ScriptHandler
{
    /**
     * @param Event $event
     */
    public static function initialize(Event $event)
    {
        $fs = new Filesystem();
        $path = __DIR__ . '/../../FrontendBundle/Resources/public/mix-manifest.json';

        if (!$fs->exists($path)) {
            $fs->touch($path);
            $content = '{"js/app.js": "js/app.js", "css/app.css": "css/app.css"}';
            file_put_contents($path, $content);
        }
    }
}
