<?php

namespace App\Providers;

use App\Services\Ai\AiProviderContract;
use App\Services\Ai\GeminiProvider;
use App\Services\Ai\OpenAiProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Admin keys (Settings & API) first, rotating; the .env key is the last fallback.
        $this->app->bind(AiProviderContract::class, function () {
            $provider = config('ai.provider') === 'openai' ? 'openai' : 'gemini';

            return new \App\Services\Ai\RotatingAiProvider(
                $provider,
                fn (string $key) => $provider === 'openai' ? new OpenAiProvider($key) : new GeminiProvider($key),
                config("ai.{$provider}.key"),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Short, stable type names in content_meta / ai_drafts (same names audit_logs uses).
        Relation::morphMap([
            'birthday_message' => \App\Models\BirthdayMessage::class,
            'name_entry' => \App\Models\NameEntry::class,
            'urdu_content' => \App\Models\UrduContent::class,
            'message' => \App\Models\Message::class,
            'quiz' => \App\Models\Quiz::class,
            'game' => \App\Models\Game::class,
            'wallpaper' => \App\Models\Wallpaper::class,
            'ai_tool' => \App\Models\AiTool::class,
            'social_post' => \App\Models\SocialPost::class,
        ]);
    }
}
