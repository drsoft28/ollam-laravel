<?php
namespace Drsoft28\OllamaLaravel;

use Illuminate\Support\ServiceProvider;

class OllamaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/ollama.php' => config_path('ollama.php'),
        ], 'config');
        $this->app->bind('ollama', function () {
            return new OllamaClient(config('ollama.base_url'), config('ollama.model'));
        });
    
        $this->app->booted(function () {
            class_alias(\Drsoft28\OllamaLaravel\Facades\Ollama::class, 'Ollama');
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ollama.php', 'ollama');

        $this->app->singleton('ollama', function ($app) {
            return new OllamaClient(config('ollama.base_url'), config('ollama.model'));
        });
    }
}