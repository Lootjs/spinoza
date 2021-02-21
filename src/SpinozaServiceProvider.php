<?php

namespace Loot\Spinoza;

use Illuminate\Support\ServiceProvider;
use Loot\Spinoza\Commands\GenerateDocs;
use Loot\Spinoza\Parsers\EventParser;
use Loot\Spinoza\Parsers\RouteParser;

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

    public function register()
    {
        $this->app->singleton(SpinozaWriter::class, static function ($app) {
            return new SpinozaWriter($app[CacheManager::class], $app[EventParser::class], $app[RouteParser::class]);
        });
    }
}
