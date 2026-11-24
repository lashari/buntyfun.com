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
            'label' => 'Kids',
            'title' => 'Birthday Wishes for Kids',
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

        'son' => [
            'label' => 'Son',
            'title' => 'Birthday Wishes for Your Son',
            'intro' => 'Sons often grow quieter about feelings as they get older, but they still read every word their parents write. A birthday message is a rare chance to say plainly that you are proud of him. Pick a wish that fits his age — playful for a young boy, respectful and warm for a teenager or a grown man — and add one thing he did this year.',
            'tips' => [
                'Name a specific moment you were proud of: a match, an exam, the way he helped a sibling or a grandparent.',
                'For teenagers, keep public posts short and light; send the emotional message privately.',
                'A du\'a at the end suits many families and is something he may remember for years.',
                'If he lives away for study or work, mention when you will next see him.',
            ],
            'avoid' => 'Avoid turning the message into advice about marks, career or marriage. Today is for praise, not plans.',
            'example' => 'Short wish + pride: "Happy birthday, beta. The way you looked after Dadi this year — I noticed, and I\'m proud of you."',
        ],
        'daughter' => [
            'label' => 'Daughter',
            'title' => 'Birthday Wishes for Your Daughter',
            'intro' => 'A birthday message to your daughter is often kept for years, screenshotted, or read again on a hard day. Tell her what you admire about her, not just that you love her. The wishes below run from playful to deeply emotional; choose the one that sounds like you and make it hers with a memory from this year.',
            'tips' => [
                'Praise who she is — brave, kind, funny, determined — as well as what she achieved.',
                'Older daughters especially value hearing that home is always there for them.',
                'A photo from her childhood paired with a short wish makes a lovely family-group post.',
                'Urdu wishes work well for daughters who are close to grandparents; the whole family can read them.',
            ],
            'avoid' => 'Avoid comments about weight, looks or marriage timelines, even as jokes.',
            'example' => 'Emotional wish + detail: "…and the day you drove us home after your first lesson, I was terrified and so proud."',
        ],
        'boss' => [
            'label' => 'Boss',
            'title' => 'Birthday Wishes for Your Boss',
            'intro' => 'Wishing your boss a happy birthday is a small, professional courtesy, and it matters more when it is specific. The wishes below are respectful enough for email, a team chat or a shared card. Formal wishes are safe for almost any manager; the lighter ones are for bosses who already joke with the team.',
            'tips' => [
                'Thank them for one real thing: guidance on a project, flexibility during a family emergency, a chance they gave you.',
                'In a group card, one line and your name is plenty.',
                'Use "sir" or "ma\'am" only if that is how you normally address them.',
                'Send it during working hours, not late at night or on their day off.',
            ],
            'avoid' => 'Avoid jokes about age, personal life or anything that sounds like flattery for a raise.',
            'example' => 'Formal wish + specific thanks: "Happy birthday! Thank you for backing the new process in March — it has made all our lives easier."',
        ],
        'grandfather' => [
            'label' => 'Grandfather',
            'title' => 'Birthday Wishes for Your Grandfather',
            'intro' => 'For a Dada or Nana, a birthday wish is often less about the day and more about saying thank you for a lifetime. Short, warm messages with a du\'a are usually the best fit, and many grandparents prefer them read aloud or written in Urdu. Use a wish below and add a memory: a lesson he taught you or a story he always tells.',
            'tips' => [
                'Read the message to him on a call or in person; many grandparents do not read long texts on phones.',
                'Mention a story he tells or a lesson he taught you — it shows you were listening.',
                'An Urdu wish or a du\'a for health is often appreciated more than a funny line.',
                'Ask the family to record a short video together instead of sending separate texts.',
            ],
            'avoid' => 'Avoid jokes about memory, health or age.',
            'example' => 'Short wish + memory: "Happy birthday, Dada jaan. I still say Bismillah before every exam, just like you taught me."',
        ],
        'grandmother' => [
            'label' => 'Grandmother',
            'title' => 'Birthday Wishes for Your Grandmother',
            'intro' => 'Dadi and Nani are often the heart of the family: the cook, the storyteller and the one whose du\'as follow everyone. A birthday message to your grandmother should feel warm and simple. Choose a wish below, mention her food or a saying of hers, and if you can, say it to her on a call rather than only sending a text.',
            'tips' => [
                'Her cooking, her stories and her du\'as are perfect details to mention.',
                'Many grandmothers prefer Urdu; one sincere line in Urdu can mean more than a long English message.',
                'Put the grandchildren on a video call together for a few minutes — that is often the real gift.',
                'Print a family photo with the wish written on the back.',
            ],
            'avoid' => 'Avoid jokes about her age, health or memory.',
            'example' => 'Heartfelt wish + detail: "Happy birthday, Nani. No one makes kheer like yours — please teach me before Eid."',
        ],
        'girlfriend' => [
            'label' => 'Girlfriend',
            'title' => 'Birthday Wishes for Your Girlfriend',
            'intro' => 'A birthday message for your girlfriend should sound like you, not like a greeting card. The wishes below range from playful to romantic. Pick one that fits how you two talk, then add something only you would know: the place you first met, a song, or a plan for the day.',
            'tips' => [
                'Mention a real memory or an inside joke — it turns any wish into a personal one.',
                'Pair the message with a plan: dinner, a walk, her favourite dessert.',
                'Keep the most romantic lines for a private message; a short wish is enough for a public post.',
                'Send it at midnight or first thing in the morning so it is one of the first she reads.',
            ],
            'avoid' => 'Avoid jokes about her age, weight or past relationships.',
            'example' => 'Short wish + plan: "Happy birthday, beautiful. Saturday: your favourite café, my treat, no phones."',
        ],
        'boyfriend' => [
            'label' => 'Boyfriend',
            'title' => 'Birthday Wishes for Your Boyfriend',
            'intro' => 'Birthday wishes for your boyfriend can be sweet, funny or a mix of both. What makes one feel real is a detail from your time together. Choose a wish below, add a memory or a plan for the day, and send it early so it is one of the first messages he sees.',
            'tips' => [
                'A funny line followed by one sincere sentence is a good balance for most couples.',
                'Mention something he worked hard on this year and say you are proud of him.',
                'Keep private details out of public posts.',
                'A voice note reading the wish can feel more personal than text.',
            ],
            'avoid' => 'Avoid jokes that compare him with others or poke at insecurities.',
            'example' => 'Funny wish + sincere line: "…Okay, seriously: thank you for being my calm this year. Happy birthday."',
        ],
    ];

    public static function for(string $relationship): ?array
    {
        return self::ALL[$relationship] ?? null;
    }
}
