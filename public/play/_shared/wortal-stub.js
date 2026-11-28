/* BuntyFun: the Wortal portal SDK these games were packaged with is not loaded.
   This no-op stand-in keeps the game code working: no ads, no analytics, no network calls.
   An "ad break" simply runs the game's continue callback straight away. */
(function () {
  var noop = function () { return Promise.resolve(); };
  var run = function (fn) { if (typeof fn === 'function') setTimeout(fn, 0); return Promise.resolve(); };
  window.Wortal = {
    isInitialized: true,
    analytics: new Proxy({}, { get: function () { return noop; } }),
    ads: {
      showInterstitial: function (placement, desc, beforeAd, afterAd) { return run(afterAd); },
      showRewarded: function (desc, beforeAd, afterAd, adDismissed, adViewed) { if (typeof adViewed === 'function') setTimeout(adViewed, 0); return run(afterAd); }
    },
    setLoadingProgress: noop, startGameAsync: noop, initializeAsync: noop
  };
})();
