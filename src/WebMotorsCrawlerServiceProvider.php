<?php

namespace EvoDev\WebMotorsCrawler;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use EvoDev\WebMotorsCrawler\Console\InstallCommand;

/**
 * Class WebMotorsCrawlerServiceProvider
 * @package EvoDev\WebMotorsCrawler
 */
class WebMotorsCrawlerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('evodev-web-motors-crawler', function ($app) {
            return new WebMotorsCrawler;
        });

        $this->app->register(\Ixudra\Curl\CurlServiceProvider::class);

        $this->app->booting(function() {
            $loader = AliasLoader::getInstance();
            $loader->alias("Curl", \Ixudra\Curl\Facades\Curl::class);
        });

        $this->registerCommands();
    }

    public function boot()
    {
        $this->registerMigrations();
    }


    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }

    protected function registerCommands()
    {
        $this->commands([
            InstallCommand::class
        ]);
    }
}
