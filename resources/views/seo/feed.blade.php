<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>BuntyFun — new wishes, messages and quizzes</title>
    <link>{{ route('home') }}</link>
    <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
    <description>Newest birthday wishes, messages, Urdu content, Islamic names and quizzes on BuntyFun.</description>
    <language>en</language>
@foreach ($items as $item)
    <item>
        <title>{{ $item['title'] }}</title>
        <link>{{ $item['link'] }}</link>
        <guid isPermaLink="true">{{ $item['link'] }}</guid>
        <description>{{ Str::limit($item['text'], 300) }}</description>
        <pubDate>{{ $item['date']->toRssString() }}</pubDate>
    </item>
@endforeach
</channel>
</rss>
