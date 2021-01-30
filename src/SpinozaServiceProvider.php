<?php

namespace Loot\Spinoza;

use Illuminate\Support\ServiceProvider;
use Loot\Spinoza\Commands\GenerateDocs;

class SpinozaServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    private $commands = [
        GenerateDocs::class,
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }
}
