/* BuntyFun Emoji Guess — original puzzles. Read the emojis, pick the answer. */
(function () {
  'use strict';
  var root = document.getElementById('emoji'); if (!root) return;
  var P = {
    'Food': [['🍕🇮🇹', 'Pizza'], ['🍚🍗🌶️', 'Biryani'], ['🫖🥛', 'Milk tea'], ['🍔🍟', 'Burger and fries'], ['🥭🥤', 'Mango shake'], ['🍳🥓🍞', 'Breakfast'], ['🍜🥢', 'Noodles'], ['🍦🍫', 'Chocolate ice cream'], ['🥔🔺', 'Samosa'], ['🍞🧈', 'Bread and butter'], ['🍓🍰', 'Strawberry cake'], ['🥜🧈', 'Peanut butter'], ['🍿🎬', 'Popcorn'], ['🍌🥤', 'Banana shake'], ['🌽🔥', 'Roasted corn']],
    'Sayings': [['⏰💰', 'Time is money'], ['🐦🐦🪨', 'Two birds with one stone'], ['🍎📅👨‍⚕️', 'An apple a day keeps the doctor away'], ['💪🧠', 'Mind over muscle'], ['🐢🏁🐇', 'Slow and steady wins the race'], ['☔🐱🐶', 'Raining cats and dogs'], ['🧊🔨', 'Break the ice'], ['🌧️🌈', 'Every cloud has a silver lining'], ['👀👂', 'Seeing is believing'], ['🤐🥇', 'Silence is golden'], ['🔥➡️💨', 'No smoke without fire'], ['🐘🏠', 'Elephant in the room'], ['🍰🍽️', 'Piece of cake'], ['🗣️🎯', 'Hit the nail on the head'], ['🌙🌞', 'Night and day']],
    'Places': [['🗼🥐', 'Paris'], ['🕌🌙🇵🇰', 'Pakistan'], ['🗽🍎', 'New York'], ['🏔️🇳🇵', 'Nepal'], ['🐫🏜️🔺', 'Egypt'], ['🗻🍣', 'Japan'], ['🦘🏖️', 'Australia'], ['🍁🏒', 'Canada'], ['🕰️☔🇬🇧', 'London'], ['🍝🛶', 'Venice'], ['🐼🧱', 'China'], ['⚽☕🇧🇷', 'Brazil'], ['🧀🍫🏔️', 'Switzerland'], ['🌷🚲', 'Netherlands'], ['🕋🤲', 'Makkah']],
    'Jobs': [['👨‍⚕️💉', 'Doctor'], ['👩‍🏫📚', 'Teacher'], ['👨‍🍳🍲', 'Chef'], ['👨‍✈️✈️', 'Pilot'], ['👨‍🚒🔥', 'Firefighter'], ['👮🚓', 'Police officer'], ['👨‍🌾🌾', 'Farmer'], ['👩‍💻🖥️', 'Programmer'], ['💇✂️', 'Barber'], ['🦷🪥', 'Dentist'], ['📸👩', 'Photographer'], ['🎨🖌️', 'Painter'], ['🔧🚿', 'Plumber'], ['📰✍️', 'Journalist'], ['🚀👨‍🚀', 'Astronaut']],
    'Things': [['☀️🕶️', 'Sunglasses'], ['🌧️☂️', 'Umbrella'], ['⭐🐟', 'Starfish'], ['🌻🌞', 'Sunflower'], ['🔥🪵', 'Campfire'], ['🍿🌽', 'Popcorn'], ['🦋🐛', 'Caterpillar'], ['🌈🦄', 'Unicorn'], ['❄️⛄', 'Snowman'], ['🏠🌳', 'Treehouse'], ['🐝🍯', 'Honeybee'], ['✈️🛄', 'Airport'], ['🔑⛓️', 'Lock and key'], ['🌙🌟', 'Night sky'], ['🦷🧚', 'Tooth fairy']]
  };
  var cat = 'Food', qs = [], i = 0, score = 0, picked = null, LEN = 10, timerOn = false;
  function shuffle(a) { a = a.slice(); for (var k = a.length - 1; k > 0; k--) { var j = Math.floor(Math.random() * (k + 1)); var t = a[k]; a[k] = a[j]; a[j] = t; } return a; }
  function build() {
    var pool = cat === 'Mix' ? Object.keys(P).reduce(function (a, k) { return a.concat(P[k].map(function (x) { return x.concat(k); })); }, []) : P[cat].map(function (x) { return x.concat(cat); });
    qs = shuffle(pool).slice(0, LEN).map(function (q) {
      var wrong = shuffle(P[q[2]].filter(function (x) { return x[1] !== q[1]; })).slice(0, 3).map(function (x) { return x[1]; });
      return { e: q[0], a: q[1], opts: shuffle(wrong.concat(q[1])) };
    });
    i = 0; score = 0; picked = null; render();
    if (window.BF) BF.played({ url: location.pathname, title: 'Emoji Guess', type: 'game' });
  }
  function render() {
    root.querySelectorAll('[data-cat]').forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.cat === cat); });
    var end = i >= qs.length;
    root.querySelector('[data-play]').hidden = end;
    root.querySelector('[data-end]').hidden = !end;
    if (end) {
      var best = window.BF ? BF.best('emoji_' + cat, score) : { best: score, isNew: false };
      root.querySelector('[data-final]').textContent = 'You scored ' + score + ' / ' + qs.length + (best.isNew ? ' — new personal best! 🏆' : ' · Best: ' + best.best);
      return;
    }
    var q = qs[i];
    root.querySelector('[data-count]').textContent = 'Question ' + (i + 1) + ' of ' + qs.length + ' · Score ' + score;
    root.querySelector('[data-emoji]').textContent = q.e;
    root.querySelector('[data-opts]').innerHTML = q.opts.map(function (o) {
      var st = '';
      if (picked !== null) st = o === q.a ? 'background:#bbf7d0;border-color:#16a34a' : (o === picked ? 'background:#fecaca;border-color:#dc2626' : 'opacity:.6');
      return '<button type="button" data-o="' + o.replace(/"/g, '&quot;') + '" class="rounded-xl border-2 border-slate-200 bg-white px-4 py-3 font-semibold" style="' + st + '">' + o + '</button>';
    }).join('');
    root.querySelector('[data-nextq]').hidden = picked === null;
  }
  root.querySelector('[data-opts]').addEventListener('click', function (e) {
    var b = e.target.closest('[data-o]'); if (!b || picked !== null) return;
    picked = b.dataset.o; if (picked === qs[i].a) score++; render();
  });
  root.querySelector('[data-nextq]').addEventListener('click', function () { i++; picked = null; render(); });
  root.querySelectorAll('[data-cat]').forEach(function (b) { b.addEventListener('click', function () { cat = b.dataset.cat; build(); }); });
  root.querySelector('[data-again]').addEventListener('click', build);
  root.querySelector('[data-share]').addEventListener('click', function () { if (window.BF) BF.share('I got ' + score + '/' + qs.length + ' in BuntyFun Emoji Guess (' + cat + ') 🤔 Can you beat me?'); });
  build();
})();
