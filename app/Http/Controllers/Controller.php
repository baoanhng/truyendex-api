<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function getCurrentHostAndScheme()
    {
        return request()->getScheme() . '://' . ltrim(str_replace('api', '', request()->getHost()), '.');
    }
}
