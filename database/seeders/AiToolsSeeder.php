<?php

namespace Database\Seeders;

use App\Models\AiTool;
use Illuminate\Database\Seeder;

class AiToolsSeeder extends Seeder
{
    /**
     * The 10 initial tools from the spec. Each prompt_template is a real,
     * reviewed instruction to the model, not a placeholder — it bounds tone
     * (clean, short, no real people targeted) directly in the prompt, since
     * there's no moderation API wired up yet (see docs/ARCHITECTURE.md).
     *
     * Published directly: unlike birthday_messages/name_entries, there's no
     * fixed authored text to pre-review here — what ships is the prompt
     * template (reviewable now, by me) plus a live API call per request.
     */
    public function run(): void
    {
        $tools = [
            [
                'slug' => 'joke-generator',
                'title' => 'Funny Joke Generator',
                'description' => 'Get a clean, original joke about anything.',
                'prompt_template' => "Write one short, original, family-friendly joke about: {input}\n\nKeep it to 2-3 sentences. No slurs, no targeting real named people, nothing explicit. Just the joke, no preamble.",
            ],
            [
                'slug' => 'roast-generator',
                'title' => 'Funny Roast Generator',
                'description' => 'A lighthearted, good-natured roast — never mean-spirited.',
                'prompt_template' => "Write one short, playful, good-natured roast about: {input}\n\nKeep it affectionate and funny, not cruel — the kind of ribbing you'd give a friend, not an insult. 2-3 sentences. No slurs, no targeting protected characteristics. Just the roast, no preamble.",
            ],
            [
                'slug' => 'nickname-generator',
                'title' => 'Nickname Generator',
                'description' => 'Fun nickname ideas based on a name, trait or hobby.',
                'prompt_template' => "Suggest 5 short, fun, clean nicknames based on: {input}\n\nOne per line, no numbering, no explanations — just the 5 nicknames.",
            ],
            [
                'slug' => 'caption-generator',
                'title' => 'Funny Caption Generator',
                'description' => 'A witty caption for your photo or moment.',
                'prompt_template' => "Write one short, witty, clean social-media caption for: {input}\n\nOne sentence, no hashtags, no emoji spam — just the caption.",
            ],
            [
                'slug' => 'birthday-message-generator',
                'title' => 'Birthday Message Generator',
                'description' => 'A personalized birthday message for anyone.',
                'prompt_template' => "Write one warm, original birthday message for: {input}\n\n2-3 sentences, genuine and specific if details are given, clean language. Just the message, no preamble.",
            ],
            [
                'slug' => 'pickup-line-generator',
                'title' => 'Pickup Line Generator',
                'description' => 'A cheesy, fun pickup line.',
                'prompt_template' => "Write one short, cheesy, clean, PG-rated pickup line related to: {input}\n\nOne or two sentences, playful not crude. Just the line, no preamble.",
            ],
            [
                'slug' => 'story-generator',
                'title' => 'Funny Story Generator',
                'description' => 'A short, silly story from a few words.',
                'prompt_template' => "Write one short, funny, clean micro-story (under 100 words) based on: {input}\n\nFamily-friendly, no violence or explicit content. Just the story, no preamble.",
            ],
            [
                'slug' => 'challenge-generator',
                'title' => 'Random Challenge Generator',
                'description' => 'A fun, safe challenge to try today.',
                'prompt_template' => "Suggest one short, fun, completely safe and legal personal challenge related to: {input}\n\nSomething doable in a day, no risk of injury or harm to self/others/property. One or two sentences. Just the challenge, no preamble.",
            ],
            [
                'slug' => 'bio-generator',
                'title' => 'Social Bio Generator',
                'description' => 'A short, punchy bio for your social profile.',
                'prompt_template' => "Write one short, punchy, clean social-media bio (under 150 characters) based on: {input}\n\nNo hashtags, no emoji spam. Just the bio, no preamble.",
            ],
            [
                'slug' => 'greeting-generator',
                'title' => 'Greeting Message Generator',
                'description' => 'A warm greeting for any occasion.',
                'prompt_template' => "Write one short, warm, original greeting message for: {input}\n\n1-2 sentences, genuine and clean. Just the greeting, no preamble.",
            ],
        ];

        foreach ($tools as $tool) {
            AiTool::updateOrCreate(['slug' => $tool['slug']], [...$tool, 'status' => 'published']);
        }
    }
}
