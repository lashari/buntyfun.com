/* BuntyFun Typing Speed Test — original passages. Words per minute (5 characters = 1 word) and accuracy. */
(function () {
  'use strict';
  var root = document.getElementById('typing'); if (!root) return;
  var TEXT = {
    easy: ['the sun is warm and the sky is blue so we walk to the park and play with a red ball under a big green tree', 'my cat likes to sleep on the sofa all day and wakes up only when she hears the sound of her food bowl', 'we eat rice and daal for lunch then drink cold water and rest for a short time before we go back to work', 'a small bird sat on the wall and sang a happy song while the children ran past on their way to school'],
    medium: ['Practice a little every day and your typing speed will grow faster than you expect. Keep your eyes on the screen, not on your hands.', 'The monsoon arrived early this year, filling the streets with puddles and the air with the smell of wet earth and fresh mangoes.', 'Good habits are built slowly: one glass of water, one page of reading and one short walk at a time, until they feel natural.', 'Cricket fans stayed up late to watch the final over, holding their breath as the bowler ran in for the last ball of the match.'],
    hard: ['Quickly judging the exact size of a jigsaw piece, Zoya fixed the puzzle\'s final corner in 47 seconds, beating her brother\'s record of 1:02.', 'On 14 August, flags appear on rooftops, bicycles and car windows; bakeries sell green-and-white cakes, and families visit relatives late into the night.', 'Typing "accurately" matters more than typing quickly: each mistake costs time, because you must notice it, press Backspace and type the word again.', 'The Karakoram Highway (about 1,300 km long) links Hasan Abdal to Khunjerab Pass, which sits roughly 4,693 metres above sea level.']
  };
  var level = 'easy', dur = 60, target = '', typed = '', startAt = 0, timer = null, left = 60, state = 'ready';
  var box = root.querySelector('[data-text]'), input = root.querySelector('[data-input]');
  function pick() {
    var list = TEXT[level].slice().sort(function () { return Math.random() - .5; });
    target = list.join(' ');
  }
  function reset() {
    clearInterval(timer); pick(); typed = ''; left = dur; state = 'ready'; input.value = ''; input.disabled = false;
    root.querySelector('[data-end]').hidden = true; render(); stats();
    root.querySelectorAll('[data-level]').forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.level === level); });
    root.querySelectorAll('[data-dur]').forEach(function (b) { b.setAttribute('aria-pressed', +b.dataset.dur === dur); });
  }
  function esc(s) { return s.replace(/&/g, '&amp;').replace(/</g, '&lt;'); }
  function render() {
    var h = '';
    var upto = Math.min(target.length, typed.length + 120);
    for (var i = Math.max(0, typed.length - 60); i < upto; i++) {
      var ch = esc(target[i]);
      if (i < typed.length) h += '<span style="color:' + (typed[i] === target[i] ? '#15803d' : '#dc2626;background:#fee2e2') + '">' + ch + '</span>';
      else if (i === typed.length) h += '<span style="background:#fde68a;border-radius:2px">' + ch + '</span>';
      else h += '<span style="color:#475569">' + ch + '</span>';
    }
    box.innerHTML = h;
  }
  function counts() {
    var ok = 0; for (var i = 0; i < typed.length; i++) if (typed[i] === target[i]) ok++;
    var mins = Math.max((Date.now() - startAt) / 60000, 1 / 60);
    return { wpm: state === 'ready' ? 0 : Math.round(ok / 5 / mins), acc: typed.length ? Math.round(100 * ok / typed.length) : 100 };
  }
  function stats() {
    var c = counts();
    root.querySelector('[data-live]').textContent = '⏱ ' + left + 's · ' + c.wpm + ' WPM · ' + c.acc + '% accuracy';
    var b = window.BF ? BF.getBest('typing_' + level + '_' + dur) : null;
    root.querySelector('[data-best]').textContent = b ? 'Your best (' + level + ', ' + dur + 's): ' + b + ' WPM' : '';
  }
  function finish() {
    clearInterval(timer); state = 'done'; input.disabled = true;
    var c = counts(); var res = window.BF ? BF.best('typing_' + level + '_' + dur, c.wpm) : { isNew: false };
    root.querySelector('[data-final]').textContent = c.wpm + ' WPM with ' + c.acc + '% accuracy' + (res.isNew ? ' — new personal best! 🏆' : '');
    root.querySelector('[data-end]').hidden = false;
    root.dataset.wpm = c.wpm; root.dataset.acc = c.acc;
    stats();
  }
  input.addEventListener('input', function () {
    if (state === 'done') return;
    if (state === 'ready') {
      state = 'run'; startAt = Date.now(); var end = startAt + dur * 1000;
      timer = setInterval(function () { left = Math.max(0, Math.ceil((end - Date.now()) / 1000)); stats(); if (!left) finish(); }, 250);
      if (window.BF) BF.played({ url: location.pathname, title: 'Typing Speed Test', type: 'game' });
    }
    typed = input.value.slice(0, target.length);
    render(); stats();
    if (typed.length >= target.length) finish();
  });
  input.addEventListener('paste', function (e) { e.preventDefault(); });
  root.querySelectorAll('[data-level]').forEach(function (b) { b.addEventListener('click', function () { level = b.dataset.level; reset(); }); });
  root.querySelectorAll('[data-dur]').forEach(function (b) { b.addEventListener('click', function () { dur = +b.dataset.dur; reset(); }); });
  root.querySelector('[data-restart]').addEventListener('click', function () { reset(); input.focus(); });
  root.querySelector('[data-share]').addEventListener('click', function () { if (window.BF) BF.share('I type ' + root.dataset.wpm + ' words per minute with ' + root.dataset.acc + '% accuracy ⌨️ Test your speed on BuntyFun'); });
  reset();
})();
