<?php

namespace App\Libraries\LineDriver\EventHandler\MessageHandler\Util;

class UrlBuilder
{
    public static function buildUrl(array $paths)
    {
        // NOTE: You should configure $baseUri according to your environment
        // Perhaps, it is prefer to use $_SERVER['HTTP_HOST'], $_SERVER['HTTP_X_FORWARDED_HOST'] or etc
        $baseUri = null;
        foreach ($paths as $path) {
            $baseUri = asset(urlencode($path));
        }
        return $baseUri;
    }
}
