<?php

/*
 * Public identity of the site, shown on About / Contact / Privacy / Terms.
 * Left empty on purpose: the owner fills these in .env — never invent them.
 */
return [
    'contact_email' => env('SITE_CONTACT_EMAIL'),
    'operator' => env('SITE_OPERATOR'),          // person or business that runs BuntyFun
    'country' => env('SITE_COUNTRY'),            // where the operator is based (governing law in Terms)
    'policies_updated' => '2026-11-20',          // bump when Privacy/Terms text changes

    // Single wish/Urdu/message pages are one short text each. They stay reachable and
    // shareable, but are noindex so search engines index the richer hub pages instead.
    'index_single_snippets' => (bool) env('SEO_INDEX_SINGLE_SNIPPETS', false),
];
