<?php

namespace EvoDev\WebMotorsCrawler\Facades;

use Illuminate\Support\Facades\Facade;

class WebMotorsCrawler extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'evodev-web-motors-crawler';
    }
}