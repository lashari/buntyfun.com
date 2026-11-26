Basic Urdu voice for BuntyFun "Listen" (used only when the visitor's device has no Urdu voice).

eSpeak NG speech synthesizer, compiled to JavaScript/WebAssembly (Emscripten):
  package  @echogarden/espeak-ng-emscripten 0.3.5 (npm), GPL-3.0 — see COPYING.txt
  source   https://github.com/echogarden-project/espeak-ng-emscripten
           https://github.com/echogarden-project/espeak-ng (branch "fork")
           https://github.com/espeak-ng/espeak-ng
Changes by BuntyFun: the data package was reduced to the files Urdu needs
(phondata, phonindex, phontab, intonations, ur_dict, lang/inc/ur); espeak-ur.js is the
unmodified build with only the package file list / name changed to match.
Runs entirely in the visitor's browser; no text is sent anywhere.
