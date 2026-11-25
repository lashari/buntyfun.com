/* English strings + t() from mashukui/web-games i18n.js (MIT), trimmed for BuntyFun: English only, no analytics, no language menu. */
(function (global) {
  'use strict';
  var EN = {
      'common.start': 'START',
      'common.playAgain': 'PLAY AGAIN',
      'common.newGame': 'NEW GAME',
      'common.shareScore': 'SHARE SCORE',
      'common.copied': '✅ Copied!',
      'common.newRecord': '🏆 NEW RECORD!',
      'common.toggleSound': 'Toggle sound',
      'common.madeBy': 'Made by',
      'common.moreGames': '🎮 More games',
      'common.freeVideo': '— free online video downloader',
      'common.bestScore': '🏅 Best score: {n}',
      'common.bestLabel': 'Best: {n}',
      'game.checkers': 'Checkers',
      'antFlap.docTitle': 'Ant Flap — Free Online Flappy Game',
      'antFlap.title': 'ANT FLAP',
      'antFlap.subtitle': 'A TINY FLYING ANT ADVENTURE',
      'antFlap.tip': 'Tap the screen or press <b>SPACE</b> to flap<br>Squeeze through the branches — don\'t crash!',
      'antFlap.first': 'First flight — good luck!',
      'antFlap.share': 'I scored {n} points in Ant Flap 🐜 Can you beat me? Play here: {url}',
      'snake.docTitle': 'Snake — Free Online Snake Game',
      'snake.title': 'SNAKE',
      'snake.subtitle': 'THE ALL-TIME CLASSIC',
      'snake.tip': 'Arrow keys / <b>WASD</b> to turn · <b>swipe</b> on mobile<br>Press <b>P</b> or <b>Esc</b> to pause',
      'snake.hudScore': 'SCORE',
      'snake.hudLevel': 'SPEED LV',
      'snake.hudLength': 'LENGTH',
      'snake.paused': 'PAUSED',
      'snake.first': 'First run — good luck!',
      'snake.share': 'I scored {n} points in Snake 🐍 Can you beat me? Play here: {url}',
      'minesweeper.docTitle': 'Minesweeper — Free Online Minesweeper Game',
      'minesweeper.hudMines': 'MINES',
      'minesweeper.hudTime': 'TIME',
      'minesweeper.newGame': 'New game',
      'minesweeper.hint': 'Left click / tap to reveal · Right click / long-press to flag 🚩',
      'minesweeper.boom': '💥 BOOM! You hit a mine.',
      'minesweeper.cleared': '🏆 CLEARED! {cols}×{rows} in {sec}s',
      'minesweeper.shareLose': 'Minesweeper got me 💥 Think you can clear it? Play here: {url}',
      'waterSort.docTitle': 'Water Sort — Free Online Liquid Sorting Puzzle',
      'waterSort.final': 'Level {n} cleared in <b>{m}</b> moves',
      'waterSort.first': 'No record yet — clear a level!',
      'waterSort.hudBest': 'BEST',
      'waterSort.hudLevel': 'LEVEL',
      'waterSort.hudMoves': 'MOVES',
      'waterSort.next': 'NEXT LEVEL',
      'waterSort.over': 'CLEARED!',
      'waterSort.restart': 'RESTART',
      'waterSort.share': 'I cleared level {n} of Water Sort 🧪 in {m} moves. Can you do better? {url}',
      'waterSort.stuck': 'No moves left — undo or restart',
      'waterSort.tip': 'Tap a tube, then tap another to pour.<br>You can only pour onto the <b>same colour</b> or into an empty tube.<br>Every deal is verified solvable.',
      'waterSort.title': 'WATER SORT',
      'waterSort.undo': 'UNDO',
      'reversi.ai': 'Computer',
      'reversi.black': 'BLACK',
      'reversi.docTitle': 'Reversi — Free Online Othello Board Game',
      'reversi.finalScore': 'Black {b} · White {w}',
      'reversi.first': 'No wins yet — good luck!',
      'reversi.modeEasy': 'EASY',
      'reversi.modeHard': 'HARD',
      'reversi.modeNormal': 'NORMAL',
      'reversi.over': 'GAME OVER',
      'reversi.overDraw': 'IT\'S A DRAW',
      'reversi.overLose': 'COMPUTER WINS',
      'reversi.overWin': 'YOU WIN!',
      'reversi.passAI': 'The computer has no legal move — it passes.',
      'reversi.passYou': 'You have no legal move — you pass.',
      'reversi.restart': 'RESTART',
      'reversi.share': 'I scored {n} at Reversi ⚪ — can you beat me? {url}',
      'reversi.tip': 'You play <b>black</b> and move first.<br>Drop a disc so it <b>flanks a line</b> of the computer\'s discs — every disc in that line flips to your colour.<br>Whoever holds the most discs at the end wins. Tap a highlighted square.',
      'reversi.title': 'REVERSI',
      'reversi.turnLegal': '{p} to move · {n} plays',
      'reversi.undo': 'UNDO',
      'reversi.white': 'WHITE',
      'reversi.wins': 'WINS',
      'reversi.you': 'You',
      'checkers.docTitle': 'Checkers — Free Online Draughts Board Game',
      'checkers.final': 'Pieces left — you {p} : {c} CPU',
      'checkers.first': 'No wins yet — take one off the computer.',
      'checkers.hudCpu': 'CPU',
      'checkers.hudWins': 'WINS',
      'checkers.hudYou': 'YOU',
      'checkers.share': 'Checkers vs CPU — {p} : {c}. Play free: {url}',
      'checkers.tip': 'You play the light pieces at the bottom and move first. Jumping is forced whenever a capture exists, and kings move all four ways. Tap a piece, then a highlighted square.',
      'snakesLadders.cpu': 'CPU',
      'snakesLadders.docTitle': 'Snakes and Ladders — Free Online Dice Board Game',
      'snakesLadders.final': 'Reached square {p} in {t} rolls',
      'snakesLadders.first': 'No wins yet — roll to start!',
      'snakesLadders.hudDice': 'DICE',
      'snakesLadders.hudTurn': 'TURN',
      'snakesLadders.hudWins': 'WINS',
      'snakesLadders.roll': 'ROLL',
      'snakesLadders.share': 'I beat the CPU at Snakes and Ladders in {t} rolls! {url}',
      'snakesLadders.tip': 'Roll the dice, climb the <b>ladders</b> and dodge the <b>snakes</b>.<br>First to square <b>100</b> wins — unlimited rolls, no exact finish needed.',
      'snakesLadders.title': 'SNAKES AND LADDERS',
      'snakesLadders.you': 'YOU',
  };
  function t(key, vars) {
    var out = EN[key];
    if (out === undefined) return key;
    if (vars) out = out.replace(/\{(\w+)\}/g, function (m, k) { return vars[k] === undefined || vars[k] === null ? m : String(vars[k]); });
    return out;
  }
  function apply(root) {
    root = root || global.document;
    var map = { 'data-i18n': 'text', 'data-i18n-html': 'html', 'data-i18n-title': 'title', 'data-i18n-aria': 'aria-label', 'data-i18n-content': 'content' };
    Object.keys(map).forEach(function (a) {
      root.querySelectorAll('[' + a + ']').forEach(function (el) {
        var v = t(el.getAttribute(a)); if (v === el.getAttribute(a)) return;
        if (map[a] === 'text') el.textContent = v; else if (map[a] === 'html') el.innerHTML = v; else el.setAttribute(map[a], v);
      });
    });
  }
  if (global.document.readyState === 'loading') global.document.addEventListener('DOMContentLoaded', function () { apply(); }); else apply();
  global.WG = { t: t, apply: apply, onChange: function () {}, current: function () { return 'en'; } };
  global.t = t;
})(window);
