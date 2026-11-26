import './bootstrap';
import './bf';
import { say, voicesFor, stopVoice, speakSeq, urduVoice, canSpeak, currentSpeech } from './player';

import Alpine from 'alpinejs';
import { listenPlayer } from './reader';

window.Alpine = Alpine;
window.BF.say = say;
window.BF.voicesFor = voicesFor;
window.BF.stopVoice = stopVoice;
window.BF.speakSeq = speakSeq;
window.BF.urduVoice = urduVoice;
window.BF.canSpeak = canSpeak;
window.BF.currentSpeech = currentSpeech;
window.BF.setVoice = (lang, id) => window.BF.store.set('voice_' + lang, id);

// Play a name: reviewed recording if there is one, otherwise the device voice.
window.BF.playName = (src, arabic, title) => {
    if (src) { window.BFPlayer.load([{ src, title, subtitle: 'Name pronunciation', key: 'name:' + title }], 0, { allowSpeed: true }); return 'audio'; }
    return say(arabic, 'ar');
};
// Quran audio URL from a reciter pattern: {g} global ayah, {sss}{aaa} padded surah/ayah.
window.BF.quranSrc = (pattern, g, s, a) => pattern.replace('{g}', g).replace('{sss}', String(s).padStart(3, '0')).replace('{aaa}', String(a).padStart(3, '0'));
window.BF.reciter = (reciters) => {
    const saved = window.BF.store.get('quran_reciter');
    return reciters.find((r) => r.slug === saved) || reciters[0] || null;
};

// Listen button on duas: Quran recitation for Quranic duas, a reviewed recording, or the device voice.
window.BF.listenDua = (listen, dua, reciters) => {
    if (listen.type === 'quran') {
        const r = window.BF.reciter(reciters);
        if (r) {
            window.BFPlayer.load(listen.verses.map((v) => ({
                src: window.BF.quranSrc(r.pattern, v.g, v.s, v.a), title: dua.title,
                subtitle: 'Quran ' + v.s + ':' + v.a + ' · ' + r.name, key: 'ayah:' + v.g,
            })), 0, { allowSpeed: false, repeatEach: 1 });
            return 'quran';
        }
    }
    if (listen.type === 'file') {
        window.BFPlayer.load([{ src: listen.src, title: dua.title, subtitle: listen.speaker ? 'Recited by ' + listen.speaker : 'Recording', key: 'dua:' + dua.slug }], 0);
        return 'file';
    }
    return say(dua.arabic, 'ar');
};

Alpine.data('listenPlayer', listenPlayer);

Alpine.start();
