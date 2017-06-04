<?php

namespace EvoDev\WebMotorsCrawler\Console;

class InstallCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'webmotors-crawler:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the database and seed';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->line("<fg=yellow>Starting data base seed</> " . getcwd());
    }
}
