<?php

namespace App\Content;

/**
 * Hand-written guidance shown on /birthday-wishes/for/{relationship}. Each hub pairs the
 * published wishes for that relationship with practical advice, so the page is useful on
 * its own and is not just a list of one-liners. Keys match birthday_messages.relationship.
 */
class BirthdayGuides
{
    public const ALL = [
        'mother' => [
            'label' => 'Mom',
            'title' => 'Birthday Wishes for Mom',
            'intro' => 'A birthday message to your mother does not need to be long to land. Most mothers remember the one specific thing you noticed far longer than a paragraph of general praise. Use the wishes below as a starting point, then add one detail only you could write.',
            'tips' => [
                'Name something concrete she does: the way she checks if you have eaten, the late-night calls, the recipes. Specific beats "best mom ever".',
                'If you live far away, say when you will next see her or call. A plan is a gift in itself.',
                'For a card or a long WhatsApp message, an emotional wish works best; for a family group chat, a short wish keeps it from feeling like a speech.',
                'If she reads Urdu more comfortably than English, send the message in Urdu, even if it is only one line.',
            ],
            'avoid' => 'Jokes about her age or appearance, even gentle ones, rarely read well in writing. Save the teasing for in person.',
            'example' => 'Take an emotional wish and add one line at the end, for example: "…and thank you for still sending me your aloo paratha recipe every time I forget it."',
        ],
        'father' => [
            'label' => 'Dad',
            'title' => 'Birthday Wishes for Dad',
            'intro' => 'Many fathers are not comfortable with big emotional messages, but almost all of them keep the ones they get. A short, sincere line with one memory usually works better than an elaborate tribute.',
            'tips' => [
                'Mention a lesson he taught you, in his own words if he has a favourite saying.',
                'A shared memory (the first time he taught you to ride a bike, drive, or fix something) gives the message weight without being sentimental.',
                'If your dad enjoys humour, a funny wish followed by one serious sentence ("Seriously though, thank you for…") is a good balance.',
                'For a public post, keep it short; save the longer message for a private text or card.',
            ],
            'avoid' => 'Avoid messages that only list what he has given you materially. Thank him for time, patience or advice instead.',
            'example' => 'Short wish + memory: "Happy birthday, Abbu. I still check the tyre pressure every Sunday, just like you taught me."',
        ],
        'best_friend' => [
            'label' => 'Best Friend',
            'title' => 'Birthday Wishes for Your Best Friend',
            'intro' => 'With a best friend you can be funnier, more personal and more honest than with anyone else, and that is exactly what makes the message feel like it came from you. Pick a wish below that matches your friendship, then add an inside joke.',
            'tips' => [
                'Reference an inside joke or a shared moment; it instantly makes a copied wish personal.',
                'Funny wishes suit a group chat or a story post; emotional ones are better sent privately.',
                'If you have been friends for years, say how many. "Twelve years and you still laugh at my worst jokes" says a lot in one line.',
                'Pair the message with a photo from a trip or an old school picture.',
            ],
            'avoid' => 'Do not post anything embarrassing publicly that your friend has not agreed to share, such as old photos or private stories.',
            'example' => 'Funny wish + inside joke: "…May this year bring you more wins than our 2019 cricket team (which, to be fair, is not hard)."',
        ],
        'friend' => [
            'label' => 'Friend',
            'title' => 'Birthday Wishes for a Friend',
            'intro' => 'For friends you see often, or friends you have lost touch with, a warm and light birthday wish is the easiest way to stay close. The wishes below are friendly without being overly intimate, so they also work for newer friendships.',
            'tips' => [
                'If you have not spoken in a while, add a line suggesting you catch up. Birthday messages are a natural reason to reconnect.',
                'Short wishes are fine for friends; you do not need to write a paragraph.',
                'Use their name at the start. It makes even a short message feel written for them.',
            ],
            'avoid' => 'Overly emotional messages can feel out of place for a casual friendship. Keep it warm and simple.',
            'example' => 'Short wish + reconnect: "Happy birthday, Sana! It has been too long. Chai this weekend?"',
        ],
        'sister' => [
            'label' => 'Sister',
            'title' => 'Birthday Wishes for Your Sister',
            'intro' => 'Messages between siblings can swing from teasing to heartfelt in a single sentence, and that mix is what makes them feel real. Choose a funny wish if you two joke around, or an emotional one if she has been there for you this year.',
            'tips' => [
                'Childhood memories (shared rooms, fights over the TV remote, covering for each other) are perfect material.',
                'If she is your older sister, thanking her for looking out for you is a meaningful touch; if younger, tell her you are proud of her.',
                'A funny wish in the family group plus a private emotional message is a nice combination.',
            ],
            'avoid' => 'Keep teasing to things she laughs about too; avoid weight, marriage or career pressure jokes.',
            'example' => 'Funny wish + memory: "…Happy birthday to the only person who knows exactly who broke the drawing-room vase in 2009."',
        ],
        'brother' => [
            'label' => 'Brother',
            'title' => 'Birthday Wishes for Your Brother',
            'intro' => 'Brothers often show affection through jokes, so a funny birthday wish is usually the safest choice. If you want to say something more serious, one sincere sentence after the joke goes a long way.',
            'tips' => [
                'A shared hobby (cricket, football, gaming, cars) is easy to work into a wish.',
                'Brothers far from home appreciate a voice note as much as a text; you can read the wish aloud.',
                'Funny wishes suit public posts; keep anything emotional for a private message if he is shy about it.',
            ],
            'avoid' => 'Avoid roasting him in front of people he works with or does not know well.',
            'example' => 'Funny wish + sincere line: "…But really, bhai, thanks for always picking up when I call."',
        ],
        'husband' => [
            'label' => 'Husband',
            'title' => 'Birthday Wishes for Your Husband',
            'intro' => 'A birthday message for your husband can be romantic, funny or quietly grateful. What makes it his is a small detail from your life together, something no template can include.',
            'tips' => [
                'Mention something he did this past year that you appreciated, big or small.',
                'A funny wish suits a public post; a romantic or emotional one is better in a card or private message.',
                'If you write a card, start with a wish below and finish with your own sentence about the year ahead.',
            ],
            'avoid' => 'Public posts with very private details can make some people uncomfortable; keep the most personal lines private.',
            'example' => 'Emotional wish + detail: "…and thank you for every early morning school run this year so I could sleep ten more minutes."',
        ],
        'wife' => [
            'label' => 'Wife',
            'title' => 'Birthday Wishes for Your Wife',
            'intro' => 'Your wife will value a message that shows you notice her: what she has carried this year, what makes her laugh, what you love about your life together. Use a wish below as the opening and make the ending yours.',
            'tips' => [
                'Name one thing she achieved or handled this year and say you are proud of her.',
                'Mention a plan: dinner, a trip, a day off. It turns words into something she can look forward to.',
                'Funny wishes work if humour is part of your relationship; otherwise choose an emotional or short one.',
            ],
            'avoid' => 'Jokes about age, cooking or appearance tend to backfire in writing.',
            'example' => 'Short wish + plan: "Happy birthday, jaan. Saturday is yours: no chores, your choice of restaurant."',
        ],
        'child' => [
            'label' => 'Son or Daughter',
            'title' => 'Birthday Wishes for Your Son or Daughter',
            'intro' => 'Birthday messages to children are often read again years later, so they are worth getting right. For young children, keep it simple and joyful; for teenagers and adults, tell them who you see them becoming.',
            'tips' => [
                'Write down one thing they did this year that made you proud. It becomes a small record of their growth.',
                'For young children, read the wish aloud or write it in their birthday card in large, clear letters.',
                'For older children, a message that respects their independence ("I love watching the person you are becoming") lands better than advice.',
            ],
            'avoid' => 'Avoid comparing them with siblings or other children, even positively.',
            'example' => 'Emotional wish + pride: "…This year you learned to swim on your own, and I have never been prouder."',
        ],
        'colleague' => [
            'label' => 'Colleague',
            'title' => 'Birthday Wishes for a Colleague',
            'intro' => 'Workplace birthday wishes should be friendly, short and suitable for everyone who might read them, for example in a team chat or on a shared card. The wishes below are written with that in mind.',
            'tips' => [
                'Keep it professional but warm; a short or lightly funny wish is ideal.',
                'Mention something about working with them ("thanks for always fixing my spreadsheets") rather than their personal life.',
                'For a manager or senior colleague, a short, polite wish is the safest choice.',
                'If you are signing a group card, add your name and one line; you do not need to write a paragraph.',
            ],
            'avoid' => 'Avoid jokes about age, appearance or personal life, and anything you would not want forwarded to HR.',
            'example' => 'Short wish + work detail: "Happy birthday, Ali! Thanks for being the calmest person in every deadline week."',
        ],
    ];

    public static function for(string $relationship): ?array
    {
        return self::ALL[$relationship] ?? null;
    }
}
