<?php

namespace App\Content;

/** What each AI Fun Studio tool expects and returns; keys are ai_tools.slug. */
class AiToolGuides
{
    public const ALL = [
        'joke-generator' => [
            'input' => 'A topic, such as "Monday mornings", "cricket", "my cat" or "exams".',
            'examples' => ['load-shedding', 'online meetings', 'my little brother'],
            'output' => 'One short, clean joke about your topic, suitable for family chats.',
            'uses' => ['Breaking the ice in a group chat', 'A light opener for a presentation', 'Something to send a friend who is having a long day'],
        ],
        'roast-generator' => [
            'input' => 'A first name plus one or two harmless habits, e.g. "Ahmed, always late, loves biryani".',
            'examples' => ['Sara, never replies on time', 'my friend who checks the fridge every 5 minutes'],
            'output' => 'A two-to-three sentence friendly roast. The tool is instructed to keep it affectionate, with no slurs and nothing targeting religion, ethnicity or other protected traits.',
            'uses' => ['Birthday banter between close friends', 'A playful caption for a group photo'],
            'note' => 'Only roast people who will laugh with you. Do not use it for real people you do not know.',
        ],
        'nickname-generator' => [
            'input' => 'A name, a trait or a hobby, e.g. "Zainab, loves painting".',
            'examples' => ['Bilal, football', 'my sister who sings all the time', 'gamer'],
            'output' => 'Five short, clean nickname ideas, one per line.',
            'uses' => ['Gaming usernames', 'Pet names for friends', 'Team names'],
        ],
        'caption-generator' => [
            'input' => 'Describe the photo or moment, e.g. "sunset at the beach with friends".',
            'examples' => ['first day at a new job', 'chai on a rainy evening', 'graduation selfie'],
            'output' => 'A short, witty caption you can paste under a photo.',
            'uses' => ['Instagram and Facebook posts', 'WhatsApp status', 'Photo albums'],
        ],
        'birthday-message-generator' => [
            'input' => 'Who it is for and one detail, e.g. "my dad, loves gardening, turning 60".',
            'examples' => ['best friend from university', 'my colleague Hina who always brings cake', 'my 10-year-old son who loves dinosaurs'],
            'output' => 'A personalised birthday message built around the detail you gave.',
            'uses' => ['When none of our ready-made wishes fits', 'A first draft you then edit in your own words'],
            'note' => 'Browse our hand-picked birthday wishes too; they are grouped by relationship with tips.',
        ],
        'pickup-line-generator' => [
            'input' => 'A theme, e.g. "maths", "coffee", "space" or "cricket".',
            'examples' => ['chemistry', 'books', 'biryani'],
            'output' => 'One cheesy, clean pickup line on your theme, meant to make someone laugh.',
            'uses' => ['A joke for a friend', 'Fun for a themed party or card'],
            'note' => 'These are jokes, not dating advice. Keep it respectful.',
        ],
        'story-generator' => [
            'input' => 'A few words or a character, e.g. "a goat who wants to be a pilot".',
            'examples' => ['a robot that is scared of the dark', 'the last samosa at a wedding', 'a cat elected mayor'],
            'output' => 'A family-friendly micro-story of under 100 words.',
            'uses' => ['Bedtime stories', 'Writing prompts', 'A laugh with friends'],
        ],
        'challenge-generator' => [
            'input' => 'A setting or mood, e.g. "at home", "with friends", "5 minutes".',
            'examples' => ['rainy day indoors', 'office lunch break', 'road trip'],
            'output' => 'One fun, safe challenge you can try today.',
            'uses' => ['Party games', 'Beating boredom', 'Friendly family competitions'],
            'note' => 'Challenges are meant to be safe; skip anything that does not feel right for you.',
        ],
        'bio-generator' => [
            'input' => 'A few facts about you, e.g. "teacher, loves travel and chai, Lahore".',
            'examples' => ['student, coder, cricket fan', 'baker, mother of two, Karachi'],
            'output' => 'A short, punchy bio for Instagram, TikTok, X or LinkedIn (edit the tone for work profiles).',
            'uses' => ['Refreshing a social profile', 'A short "about me" line'],
            'note' => 'Only include details you are happy to make public.',
        ],
        'greeting-generator' => [
            'input' => 'The occasion and the person, e.g. "Eid, my neighbours" or "new baby, cousin".',
            'examples' => ['Jummah Mubarak to family group', 'congratulations on a new job', 'get well soon to my teacher'],
            'output' => 'A warm greeting message for the occasion.',
            'uses' => ['Eid, Ramadan and festival greetings', 'Congratulations and get-well messages'],
        ],
    ];

    public static function for(string $slug): ?array
    {
        return self::ALL[$slug] ?? null;
    }
}
