<?php

namespace App\Content;

/** Quote topics (/quotes/topic/{key}). QuoteSlate tags map onto these; intro text is BuntyFun's own. */
class QuoteTopics
{
    public const MIN_FOR_INDEX = 10;

    public const ALL = [
        'motivation' => ['Motivational Quotes', 'Short lines to get you moving when work, study or training feels hard. Read one before an exam or a long day, or send one to a friend who needs a push.'],
        'inspiration' => ['Inspirational Quotes', 'Words from writers, thinkers and leaders about dreaming big and starting anyway. Good for captions, notebooks and morning messages.'],
        'wisdom' => ['Wisdom Quotes', 'Old and new sayings about living well, thinking clearly and learning from mistakes, from philosophers such as Confucius, Laozi and Marcus Aurelius to modern writers.'],
        'life' => ['Life Quotes', 'Quotes about everyday life: time, choices, family, change and finding meaning in ordinary days.'],
        'happiness' => ['Happiness Quotes', 'What makes people happy, according to the people who thought about it most. Many of these say happiness comes from gratitude and giving, not from having more.'],
        'love' => ['Love Quotes', 'Quotes about love in all its forms: between family, friends and partners, and kindness to strangers.'],
        'friendship' => ['Friendship Quotes', 'Lines about true friends, loyalty and the people who stay. Perfect to send to a best friend or write in a card.'],
        'success' => ['Success Quotes', 'What successful people said about hard work, failure, discipline and not giving up.'],
        'resilience' => ['Resilience Quotes', 'For tough times: quotes about getting back up, staying strong and growing through difficulty.'],
        'hope' => ['Hope Quotes', 'Quotes about hope, patience and better days ahead.'],
        'change' => ['Change Quotes', 'On starting again, adapting and making change happen in your own life.'],
        'courage' => ['Courage Quotes', 'Brave words about facing fear, speaking up and doing the right thing.'],
        'leadership' => ['Leadership Quotes', 'Quotes on leading people, teams and yourself: responsibility, vision and setting an example.'],
        'knowledge' => ['Knowledge and Learning Quotes', 'On reading, learning and curiosity. Good for students and teachers.'],
        'justice' => ['Justice Quotes', 'Quotes about fairness, truth and standing up for others.'],
        'mindfulness' => ['Mindfulness Quotes', 'Calm words about the present moment, patience and a quiet mind.'],
        'growth' => ['Personal Growth Quotes', 'On becoming a better version of yourself, one small step at a time.'],
        'purpose' => ['Purpose Quotes', 'On finding your reason, your work and what matters most.'],
        'integrity' => ['Integrity Quotes', 'Quotes about honesty, character and keeping your word.'],
        'kindness' => ['Kindness and Empathy Quotes', 'On empathy, forgiveness, gratitude and treating people well.'],
        'patience' => ['Patience Quotes', 'On patience, perseverance and waiting for the right time.'],
        'creativity' => ['Creativity Quotes', 'On ideas, imagination and making new things.'],
        'fear' => ['Quotes About Fear and Anger', 'Wise words on handling fear, anger and strong emotions.'],
    ];

    /** QuoteSlate tag → our topic. */
    public const MAP = [
        'empathy' => 'kindness', 'gratitude' => 'kindness', 'forgiveness' => 'kindness', 'humility' => 'wisdom', 'harmony' => 'mindfulness',
        'self-reflection' => 'wisdom', 'perseverance' => 'patience', 'anger' => 'fear', 'general' => 'wisdom',
    ];

    public static function topicFor(array $tags): string
    {
        foreach ($tags as $t) {
            $t = self::MAP[$t] ?? $t;
            if (isset(self::ALL[$t])) {
                return $t;
            }
        }

        return 'wisdom';
    }

    public static function label(string $topic): string
    {
        return self::ALL[$topic][0] ?? ucfirst($topic).' Quotes';
    }
}
