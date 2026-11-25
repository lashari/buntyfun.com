@php
    $months = [['Muharram', 'محرم'], ['Safar', 'صفر'], ["Rabi' al-Awwal", 'ربیع الاول'], ["Rabi' al-Thani", 'ربیع الثانی'], ['Jumada al-Ula', 'جمادی الاولیٰ'], ['Jumada al-Akhirah', 'جمادی الثانی'], ['Rajab', 'رجب'], ["Sha'ban", 'شعبان'], ['Ramadan', 'رمضان'], ['Shawwal', 'شوال'], ["Dhu al-Qa'dah", 'ذوالقعدہ'], ['Dhu al-Hijjah', 'ذوالحجہ']];
    $events = [
        [1, 1, 'Islamic New Year', 'The first day of Muharram begins the Hijri year, counted from the Hijrah of the Prophet ﷺ to Madinah.'],
        [1, 10, 'Day of Ashura', 'The 10th of Muharram. Fasting on this day (with the 9th or 11th) is a Sunnah; it is also the day of the martyrdom of Imam Husain at Karbala.'],
        [3, 12, '12 Rabi\' al-Awwal', 'The date most widely given for the birth of the Prophet Muhammad ﷺ; observed as Eid Milad un-Nabi in Pakistan.'],
        [7, 27, '27 Rajab (Shab-e-Miraj)', 'Commonly observed as the night of the Isra and Mi\'raj, the Night Journey.'],
        [8, 15, '15 Sha\'ban (Shab-e-Barat)', 'The middle night of Sha\'ban, observed in South Asia with prayer and remembrance.'],
        [9, 1, 'First day of Ramadan', 'The month of fasting begins. The Quran was first revealed in Ramadan.'],
        [9, 27, 'Laylat al-Qadr (27th night)', 'The Night of Power is sought in the odd nights of the last ten days of Ramadan; the 27th is widely observed.'],
        [10, 1, 'Eid al-Fitr', 'The festival at the end of Ramadan, with Eid prayer and Zakat al-Fitr given before it.'],
        [12, 8, 'Hajj begins (8 Dhu al-Hijjah)', 'Pilgrims head to Mina at the start of the Hajj days.'],
        [12, 9, 'Day of Arafah', 'The greatest day of Hajj. For those not on Hajj, fasting on this day is a Sunnah.'],
        [12, 10, 'Eid al-Adha', 'The festival of sacrifice, remembering the sacrifice of Prophet Ibrahim.'],
    ];
@endphp
<x-layout title="Islamic Calendar {{ now()->year }} — Today's Hijri Date & Important Islamic Dates | BuntyFun"
          description="Today's Hijri (Islamic) date, the 12 Islamic months in English and Urdu, and the expected dates of Ramadan, Eid al-Fitr, Eid al-Adha, Ashura and other occasions."
          :breadcrumbs="['Islamic Calendar' => null]" :urduFont="true">
    <div class="mx-auto max-w-4xl px-4 py-10" x-data="hijriCal({{ Js::from($events) }}, {{ Js::from($months) }})">
        <h1 class="text-3xl font-bold">Islamic Calendar</h1>
        <div class="mt-5 rounded-2xl bg-navy-700 p-6 text-white shadow">
            <p class="text-sm uppercase tracking-wide opacity-80">Today's Hijri date (Umm al-Qura)</p>
            <p class="mt-1 text-3xl font-bold" x-text="todayText"></p>
            <p class="font-urdu mt-1 text-2xl" dir="rtl" lang="ur" x-text="todayUr"></p>
            <p class="mt-2 text-sm opacity-80" x-text="gregText"></p>
        </div>
        <p class="mt-3 text-sm text-slate-600">Islamic months begin with the sighting of the new moon, so the date in Pakistan or your country can differ by one day from the calculated Umm al-Qura date shown here. Always follow your local moon-sighting announcement for Ramadan and Eid.</p>

        <h2 class="mt-10 text-xl font-bold">Upcoming Islamic dates</h2>
        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <template x-for="e in upcoming" :key="e.name">
                <div class="border-b border-slate-100 p-4 last:border-0">
                    <p class="flex flex-wrap items-baseline justify-between gap-2"><span class="font-semibold" x-text="e.name"></span><span class="text-sm font-semibold text-navy-600" x-text="e.when"></span></p>
                    <p class="mt-1 text-sm text-slate-600" x-text="e.about"></p>
                </div>
            </template>
        </div>

        <h2 class="mt-10 text-xl font-bold">The 12 Islamic months</h2>
        <ol class="mt-3 grid gap-2 sm:grid-cols-2">
            @foreach ($months as $i => $m)
                <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-2" :class="month === {{ $i + 1 }} ? 'ring-2 ring-brand-500' : ''">
                    <span><span class="text-slate-400">{{ $i + 1 }}.</span> {{ $m[0] }}</span>
                    <span class="font-urdu text-lg" dir="rtl" lang="ur">{{ $m[1] }}</span>
                </li>
            @endforeach
        </ol>
        <section class="prose-page mt-10">
            <h2>About the Hijri calendar</h2>
            <p>The Islamic (Hijri) calendar is lunar: each month starts with the new crescent moon and lasts 29 or 30 days, so the year is about 354 days, around 11 days shorter than the solar year. That is why Ramadan and Eid move earlier each year in the Gregorian calendar. Year 1 began with the Hijrah of the Prophet ﷺ from Makkah to Madinah in 622 CE.</p>
            <p>Four months are called sacred in the Quran (9:36): Muharram, Rajab, Dhu al-Qa'dah and Dhu al-Hijjah.</p>
            <p>See also: <a href="{{ route('duas.index') }}">daily duas</a>, <a href="{{ route('islamic.allah-names') }}">99 Names of Allah</a>, <a href="{{ route('name-entries.index') }}">Islamic names</a>.</p>
        </section>
    </div>
    <script>
        function hijriCal(events, months) {
            const fmt = (d) => { try { const p = new Intl.DateTimeFormat('en-u-ca-islamic-umalqura-nu-latn', { day: 'numeric', month: 'numeric', year: 'numeric' }).formatToParts(d); const g = (t) => +(p.find(x => x.type === t) || {}).value; return { d: g('day'), m: g('month'), y: g('year') }; } catch (e) { return null; } };
            const now = new Date();
            const h = fmt(now);
            const greg = (d) => d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' });
            const upcoming = [];
            if (h) {
                const found = {};
                for (let i = 0; i < 400 && Object.keys(found).length < events.length; i++) {
                    const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() + i);
                    const x = fmt(d);
                    events.forEach((e, k) => { if (!found[k] && x.m === e[0] && x.d === e[1]) found[k] = { name: e[2], about: e[3], when: (i === 0 ? 'Today · ' : i === 1 ? 'Tomorrow · ' : 'in ' + i + ' days · ') + greg(d) + ' (' + e[1] + ' ' + months[e[0] - 1][0] + ' ' + x.y + ' AH)', i }; });
                }
                Object.values(found).sort((a, b) => a.i - b.i).forEach(e => upcoming.push(e));
            }
            return {
                month: h ? h.m : 0,
                todayText: h ? h.d + ' ' + months[h.m - 1][0] + ' ' + h.y + ' AH' : 'Your browser cannot show the Hijri date',
                todayUr: h ? h.d + ' ' + months[h.m - 1][1] + ' ' + h.y + ' ہجری' : '',
                gregText: greg(now),
                upcoming,
            };
        }
    </script>
</x-layout>
