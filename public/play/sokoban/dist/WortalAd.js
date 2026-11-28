/* BuntyFun: Wortal portal ads removed. Same function names, no ads, no network. */
const AdTypes = { preroll: 'preroll', start: 'start', pause: 'pause', next: 'next', browse: 'browse', rewardedAd: 'reward' };
function CallAd(type, name, beforeAd, afterAd, adBreakDone) { if (afterAd) afterAd(); if (adBreakDone) adBreakDone(); }
function CallPreroll(name, adBreakDone) { if (adBreakDone) adBreakDone(); }
function RemoveBlackCover() { var c = document.getElementById('black-cover'); if (c) c.hidden = true; }
window.addEventListener('load', function () { RemoveBlackCover(); window.dispatchEvent(new Event('WortalAdLoaded')); });
