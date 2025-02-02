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
}
