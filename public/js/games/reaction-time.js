/* BuntyFun Reaction Time Test — original code. Wait for green, tap as fast as you can, 5 rounds. */
(function () {
  'use strict';
  var root = document.getElementById('react'); if (!root) return;
  var pad = root.querySelector('[data-pad]'), state = 'idle', t0 = 0, wait = null, times = [];
  function set(st, title, sub, bg) { state = st; pad.style.background = bg; pad.querySelector('[data-t]').textContent = title; pad.querySelector('[data-s]').textContent = sub; }
  function idle() { set('idle', times.length ? 'Tap to continue' : 'Tap to start', times.length ? 'Round ' + (times.length + 1) + ' of 5' : '5 rounds. Wait for green, then tap.', '#1e3a5f'); }
  function arm() {
    set('wait', 'Wait for green…', 'Do not tap yet', '#b91c1c');
    wait = setTimeout(function () { set('go', 'TAP!', '', '#16a34a'); t0 = performance.now(); }, 1200 + Math.random() * 2800);
  }
  function hit() {
    if (state === 'idle') { if (times.length === 0 && window.BF) BF.played({ url: location.pathname, title: 'Reaction Time', type: 'game' }); arm(); return; }
    if (state === 'wait') { clearTimeout(wait); set('early', 'Too early!', 'Tap to try this round again', '#f59e0b'); return; }
    if (state === 'early') { arm(); return; }
    if (state === 'go') {
      var ms = Math.round(performance.now() - t0); times.push(ms);
      if (times.length >= 5) return done();
      set('idle', ms + ' ms', 'Tap for round ' + (times.length + 1) + ' of 5', '#1e3a5f'); list();
      return;
    }
    if (state === 'done') { times = []; list(); idle(); }
  }
  function done() {
    var avg = Math.round(times.reduce(function (a, b) { return a + b; }, 0) / times.length);
    var res = window.BF ? BF.best('reaction_ms', avg, true) : { isNew: false, best: avg };
    var verdict = avg < 220 ? 'Lightning fast ⚡' : avg < 280 ? 'Very quick 👏' : avg < 350 ? 'About average' : 'Keep practising';
    set('done', avg + ' ms average', verdict + (res.isNew ? ' · New personal best! 🏆' : ' · Best: ' + res.best + ' ms') + ' · Tap to play again', '#7c3aed');
    root.dataset.avg = avg; root.querySelector('[data-share]').hidden = false; list();
  }
  function list() { root.querySelector('[data-list]').textContent = times.length ? 'Your times: ' + times.join(' ms, ') + ' ms' : ''; if (state !== 'done') root.querySelector('[data-share]').hidden = true; }
  pad.addEventListener('pointerdown', function (e) { e.preventDefault(); hit(); });
  document.addEventListener('keydown', function (e) { if (e.code === 'Space' || e.key === 'Enter') { e.preventDefault(); hit(); } });
  root.querySelector('[data-share]').addEventListener('click', function () { if (window.BF) BF.share('My reaction time is ' + root.dataset.avg + ' ms ⚡ Can you beat me on BuntyFun?'); });
  idle();
})();
