<?php

namespace App;

class Helper
{
    /**
     * Get the current host and scheme
     *
     * @return string
     */
    public static function getCurrentHostAndScheme()
    {
        return request()->getScheme() . '://' . ltrim(str_replace('api', '', request()->getHost()), '.');
    }

    /**
     *
     * @param string $provider
     * @return void
     */
    public static function verifySocialiteProvider(string $provider)
    {
        if (!in_array($provider, ['google'])) {
            abort(400, 'Invalid provider');
        }
    }
}
