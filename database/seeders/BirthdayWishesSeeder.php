<?php

namespace Database\Seeders;

use App\Models\BirthdayMessage;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BirthdayWishesSeeder extends Seeder
{
    /**
     * Each line written fresh for this batch — no templated find-and-replace across
     * relationships, no recycled stock phrasing. English only; Urdu wishes are a
     * separate Release 2 translation effort, not a machine translation of these.
     *
     * Seeded as in_review, not published — this is a first editorial draft, not a
     * signed-off one; someone should read these for tone before they go live.
     */
    public function run(): void
    {
        $category = Category::where('slug', 'birthday-wishes')->firstOrFail();

        $wishes = [
            // Best friend
            ['best_friend', 'funny', "Happy birthday to someone who's seen me at my best, my worst, and my 3 a.m. existential crises — and still picks up the phone."],
            ['best_friend', 'funny', "You're officially one year closer to agreeing with everything your doctor tells you to stop doing. Happy birthday!"],
            ['best_friend', 'funny', "They say true friends are the ones who'll bail you out of jail. I'd bail you out, but I'd also ask a lot of questions on the ride home. Happy birthday."],
            ['best_friend', 'emotional', "People come and go, but you've stayed — through every bad haircut, bad decision, and bad breakup. Thank you for being the constant. Happy birthday."],
            ['best_friend', 'emotional', "I don't think I've ever told you how much easier you make hard days just by existing in them. Happy birthday to my favorite person."],
            ['best_friend', 'short', "Here's to another year of you being effortlessly, infuriatingly great. Happy birthday."],
            ['best_friend', 'short', "Couldn't have picked a better person to grow up awkwardly alongside. Happy birthday, old friend."],

            // Friend
            ['friend', 'funny', 'Happy birthday! May your cake be bigger than your problems and your candles fewer than your excuses to skip the gym.'],
            ['friend', 'funny', "Another year older, another year of pretending you know what you're doing. Happy birthday — we believe in you anyway."],
            ['friend', 'emotional', 'Some friendships feel like home. Thanks for being one of mine. Happy birthday.'],
            ['friend', 'short', 'Hope your birthday is as good as you are. Happy birthday!'],
            ['friend', 'short', 'Cheers to you today — and to many more good years ahead.'],

            // Mother
            ['mother', 'emotional', 'Everything good I know how to do, I learned by watching you do it first. Happy birthday, Mom.'],
            ['mother', 'emotional', "You've spent your whole life making sure everyone else felt taken care of. Today, let someone take care of you. Happy birthday."],
            ['mother', 'emotional', "I don't say it enough, but I notice everything you do — and I'm endlessly grateful for it. Happy birthday, Mom."],
            ['mother', 'short', 'To the first person who ever loved me — happy birthday, Mom.'],

            // Father
            ['father', 'emotional', "You taught me how to work hard, how to laugh at myself, and how to show up even when it's inconvenient. Happy birthday, Dad."],
            ['father', 'emotional', 'I used to think you knew everything. Now I just think you tried harder than anyone else to figure it out. Happy birthday.'],
            ['father', 'emotional', 'Thanks for every lesson you never meant to teach me just by being you. Happy birthday, Dad.'],
            ['father', 'short', 'Happy birthday to the first man I ever looked up to.'],

            // Brother
            ['brother', 'funny', 'Happy birthday to the person who made my childhood 50% more chaotic and 100% more fun.'],
            ['brother', 'funny', "You're older now, which legally means I get to make fun of you for one more thing. Happy birthday."],
            ['brother', 'funny', 'We fought over the remote for 15 years and somehow still ended up best friends. Happy birthday, brother.'],
            ['brother', 'short', 'Growing up with you was never boring. Happy birthday.'],

            // Sister
            ['sister', 'funny', 'Happy birthday to the only person who can embarrass me in family photos and get away with it every single time.'],
            ['sister', 'funny', "You've borrowed my clothes, my shoes, and occasionally my identity when it was convenient. Happy birthday — I guess I forgive you."],
            ['sister', 'funny', "Here's to another year of you pretending you're the responsible sibling. Happy birthday."],
            ['sister', 'emotional', "You've been my built-in best friend since before I could choose my own. Happy birthday, sis."],
            ['sister', 'short', 'Happy birthday to my favorite built-in best friend.'],

            // Husband
            ['husband', 'emotional', 'Every year with you makes the ones before it make more sense. Happy birthday to the best decision I ever made.'],
            ['husband', 'emotional', "I fall asleep easier knowing you're the last thing I see before I close my eyes. Happy birthday, love."],
            ['husband', 'funny', "Happy birthday to the man who still can't find his keys but somehow always finds a way to make me laugh."],
            ['husband', 'funny', "You're officially one year older and still haven't figured out the thermostat. I love you anyway. Happy birthday."],
            ['husband', 'short', "Happy birthday to the one I'd choose again, every time."],

            // Wife
            ['wife', 'emotional', 'You make ordinary days feel like something worth remembering. Happy birthday to my favorite person.'],
            ['wife', 'emotional', "I didn't know what I was looking for until I found you. Happy birthday, my love."],
            ['wife', 'funny', "Happy birthday to the woman who somehow runs this entire household while I still can't find the good scissors."],
            ['wife', 'funny', "You get another year older and somehow still look like you're winning at life. Happy birthday."],
            ['wife', 'short', 'Happy birthday to the best part of every single day.'],

            // Colleague
            ['colleague', 'funny', 'Happy birthday! May your inbox be empty and your coffee always hot — at least for today.'],
            ['colleague', 'funny', 'Another year older and still the only one who remembers where the good stapler is. Happy birthday!'],
            ['colleague', 'short', 'Happy birthday — hope your day is better than your meeting schedule.'],
            ['colleague', 'short', 'Wishing you a great birthday and an even better excuse to leave early today.'],

            // Child
            ['child', 'emotional', "Watching you grow up has been the best story I've ever gotten to be part of. Happy birthday."],
            ['child', 'emotional', "However old you get, you'll always be the best thing that ever happened to me. Happy birthday."],
            ['child', 'short', 'Happy birthday to my favorite person in the whole world.'],
        ];

        $counts = [];

        foreach ($wishes as [$relationship, $tone, $body]) {
            $key = "{$relationship}-{$tone}";
            $counts[$key] = ($counts[$key] ?? 0) + 1;
            $number = str_pad((string) $counts[$key], 2, '0', STR_PAD_LEFT);
            $slugRelationship = str_replace('_', '-', $relationship);

            BirthdayMessage::updateOrCreate(
                ['slug' => "{$slugRelationship}-{$tone}-{$number}"],
                [
                    'category_id' => $category->id,
                    'relationship' => $relationship,
                    'tone' => $tone,
                    'language' => 'en',
                    'body' => $body,
                    'status' => 'in_review',
                ]
            );
        }
    }
}
