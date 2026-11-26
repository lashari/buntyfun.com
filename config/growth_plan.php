<?php

/*
| BuntyFun growth plan shown at Admin → Growth Plan.
| status: done | doing | todo | needs_you
| who:    Claude | You
| when:   MM-DD-YYYY (done = date finished, others = target date)
| Edit this file and redeploy to update the page.
*/

return [
    'updated' => '11-24-2026',

    'phases' => [
        [
            'title' => 'Foundation: site live, Google can read it',
            'items' => [
                ['task' => 'Site live on WebSouls with HTTPS, www and /index.php redirects', 'status' => 'done', 'who' => 'Claude', 'when' => '11-09-2026', 'note' => 'One clean URL per page.'],
                ['task' => 'Search Console verified and sitemap submitted', 'status' => 'done', 'who' => 'Claude', 'when' => '11-02-2026', 'note' => 'Resubmitted 11-22: 391 URLs discovered.'],
                ['task' => 'Google Analytics 4 tracking', 'status' => 'done', 'who' => 'Claude', 'when' => '11-09-2026', 'note' => 'G-72ZN0C7Q70.'],
                ['task' => 'Unique title and description on every page', 'status' => 'done', 'who' => 'Claude', 'when' => '11-12-2026', 'note' => ''],
                ['task' => 'Schema (WebSite, Breadcrumb, FAQ), Open Graph, robots, RSS feed', 'status' => 'done', 'who' => 'Claude', 'when' => '11-16-2026', 'note' => ''],
                ['task' => 'About, Contact, Privacy, Terms, mobile menu; thin pages set to noindex', 'status' => 'done', 'who' => 'Claude', 'when' => '11-20-2026', 'note' => 'AdSense trust pages.'],
                ['task' => 'New logo, brand colours, share image', 'status' => 'done', 'who' => 'Claude', 'when' => '11-21-2026', 'note' => ''],
            ],
        ],
        [
            'title' => 'Content',
            'items' => [
                ['task' => 'Birthday wishes by relationship + Urdu birthday wishes page', 'status' => 'done', 'who' => 'Claude', 'when' => '11-20-2026', 'note' => ''],
                ['task' => '10 quizzes with sources and fair answer order', 'status' => 'done', 'who' => 'Claude', 'when' => '11-20-2026', 'note' => ''],
                ['task' => '13 open-source games (MIT/BSD) with age filter', 'status' => 'done', 'who' => 'Claude', 'when' => '11-22-2026', 'note' => ''],
                ['task' => 'Wallpapers: NASA + Wikimedia import, categories, thumbnails', 'status' => 'done', 'who' => 'Claude', 'when' => '11-22-2026', 'note' => 'Public-domain / CC only.'],
                ['task' => 'New Year 2027 wishes page (64 wishes, incl. Urdu)', 'status' => 'done', 'who' => 'Claude', 'when' => '11-23-2026', 'note' => ''],
                ['task' => '9 message pages with own URL (good morning, good night, friendship, motivational, thank you, get well, anniversary, Eid, condolence): 186 hand-written messages incl. Urdu', 'status' => 'done', 'who' => 'Claude', 'when' => '11-24-2026', 'note' => 'Each category had ~1 message before.'],
                ['task' => 'Birthday wish pages: 257 new hand-written wishes (50 Urdu); 7 new pages: boss, daughter, son, grandfather, grandmother, girlfriend, boyfriend', 'status' => 'done', 'who' => 'Claude', 'when' => '11-24-2026', 'note' => 'Every relationship page now ~20–25 wishes. "Son or Daughter" page renamed "Kids".'],
                ['task' => 'Urdu corner: 6 topic pages (quotes, friendship, love, motivational, jokes, family), 105 hand-written lines; hub rebuilt', 'status' => 'done', 'who' => 'Claude', 'when' => '11-24-2026', 'note' => 'Was 18 lines in total. No poetry (copyright).'],
                ['task' => 'Read the 6 Urdu pages once (a native speaker check)', 'status' => 'needs_you', 'who' => 'You', 'when' => '11-30-2026', 'note' => 'Written by AI; fix any line that sounds unnatural in Admin → Urdu Content.'],
                ['task' => 'Wallpaper pages: real caption from NASA/Wikimedia on each page; pages without a 20+ word caption hidden from Google', 'status' => 'done', 'who' => 'Claude', 'when' => '11-24-2026', 'note' => 'Was 295 pages with ~46 words. Admin → Wallpapers → Fetch descriptions; captions can be edited per wallpaper.'],
                ['task' => 'Check the 23 Islamic names (meanings) by a person', 'status' => 'needs_you', 'who' => 'You', 'when' => '12-05-2026', 'note' => 'Published without human check; religious content needs a review.'],
                ['task' => 'Ramadan 2027 page (duas, wishes, Urdu)', 'status' => 'todo', 'who' => 'Claude', 'when' => '01-10-2027', 'note' => 'Ramadan starts ~02-08-2027; Google needs ~4 weeks.'],
                ['task' => 'Eid ul-Fitr 2027 wishes page', 'status' => 'todo', 'who' => 'Claude', 'when' => '02-10-2027', 'note' => 'Eid ~03-10-2027.'],
            ],
        ],
        [
            'title' => 'Get pages indexed',
            'items' => [
                ['task' => 'Request indexing: wallpapers + 10 games', 'status' => 'done', 'who' => 'Claude', 'when' => '11-24-2026', 'note' => 'Google quota ~10 a day.'],
                ['task' => 'Request indexing: underrun, tower-builder, block-drop, particle-clicker, tap-tap-tap', 'status' => 'todo', 'who' => 'Claude', 'when' => '11-25-2026', 'note' => ''],
                ['task' => 'Find pages "discovered, not indexed" and make them stronger or merge them', 'status' => 'todo', 'who' => 'Claude', 'when' => '11-27-2026', 'note' => '57 pages not indexed on 11-22. Biggest traffic lever.'],
                ['task' => 'Related-content links on every page (wishes ↔ quizzes ↔ games)', 'status' => 'todo', 'who' => 'Claude', 'when' => '11-30-2026', 'note' => 'Helps Google find and keep pages.'],
            ],
        ],
        [
            'title' => 'Bring visitors back daily',
            'items' => [
                ['task' => 'Daily page: quiz of the day, riddle, fun fact, featured game', 'status' => 'todo', 'who' => 'Claude', 'when' => '11-27-2026', 'note' => 'No login; changes every day.'],
                ['task' => 'Recently played games + favourites (saved in the browser)', 'status' => 'done', 'who' => 'Claude', 'when' => '11-26-2026', 'note' => 'Star button on every game and quiz; lists on Home, Games, Quizzes.'],
                ['task' => 'Track quiz completions, game starts, copy and WhatsApp clicks in GA4', 'status' => 'todo', 'who' => 'Claude', 'when' => '12-01-2026', 'note' => 'So we know what people use.'],
            ],
        ],
        [
            'title' => 'Social sharing',
            'items' => [
                ['task' => 'Facebook + Instagram posting queue with approval', 'status' => 'done', 'who' => 'Claude', 'when' => '11-16-2026', 'note' => 'Admin → Social.'],
                ['task' => 'Check first New Year post went live on FB/IG', 'status' => 'todo', 'who' => 'Claude', 'when' => '11-25-2026', 'note' => '1 PM PKT.'],
                ['task' => 'Create a free Pinterest developer app', 'status' => 'needs_you', 'who' => 'You', 'when' => '12-01-2026', 'note' => 'developers.pinterest.com. Wallpapers + wishes suit Pinterest best.'],
                ['task' => 'Pinterest posting in Admin → Social', 'status' => 'todo', 'who' => 'Claude', 'when' => '12-04-2026', 'note' => 'After the Pinterest app exists.'],
            ],
        ],
        [
            'title' => 'Numbers in admin',
            'items' => [
                ['task' => 'Create a free Google service account with read access to GA4 + Search Console', 'status' => 'needs_you', 'who' => 'You', 'when' => '12-02-2026', 'note' => 'console.cloud.google.com; Claude can guide step by step.'],
                ['task' => 'Growth dashboard: visitors, organic clicks, top pages, week vs week', 'status' => 'todo', 'who' => 'Claude', 'when' => '12-06-2026', 'note' => 'After the service account.'],
                ['task' => 'Keyword list from real Search Console queries', 'status' => 'todo', 'who' => 'Claude', 'when' => '12-08-2026', 'note' => 'Real data only, no guessed keywords.'],
            ],
        ],
        [
            'title' => 'AdSense',
            'items' => [
                ['task' => 'Get AdSense publisher ID (ca-pub) and add it to .env', 'status' => 'needs_you', 'who' => 'You', 'when' => '12-10-2026', 'note' => 'Turns on /ads.txt.'],
                ['task' => 'Set up AdSense consent message (EEA/UK + US states)', 'status' => 'needs_you', 'who' => 'You', 'when' => '12-10-2026', 'note' => 'AdSense → Privacy & messaging.'],
                ['task' => 'Ad placement on/off controls with category exclusions', 'status' => 'done', 'who' => 'Claude', 'when' => '11-26-2026', 'note' => 'Admin → Ads: sections, devices, units per page, excluded pages. Quran off by default.'],
                ['task' => 'Apply for AdSense', 'status' => 'needs_you', 'who' => 'You', 'when' => '12-15-2026', 'note' => 'Approval is up to Google.'],
            ],
        ],
        [
            'title' => 'Review',
            'items' => [
                ['task' => 'Weekly Search Console + GA4 check', 'status' => 'doing', 'who' => 'Claude', 'when' => 'Every Monday', 'note' => ''],
                ['task' => 'Month review and next-month plan', 'status' => 'todo', 'who' => 'Claude', 'when' => '12-24-2026', 'note' => ''],
            ],
        ],
    ],
];
