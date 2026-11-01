<?php

namespace App\Providers;

use App\Services\Ai\AiProviderContract;
use App\Services\Ai\GeminiProvider;
use App\Services\Ai\OpenAiProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiProviderContract::class, function () {
            return match (config('ai.provider')) {
                'openai' => new OpenAiProvider(config('ai.openai.key')),
                default => new GeminiProvider(config('ai.gemini.key')),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
