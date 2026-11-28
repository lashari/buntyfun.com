// Fullscreen always starts inside the button's click handler (a user gesture).
export class FullscreenControl {
  constructor(button, status, onResize, doc = document) {
    this.button = button; this.status = status; this.doc = doc;
    this.onResize = onResize; this.pending = false;
    this._change = () => { this.refresh(); onResize(); };
    this._error = () => { this.message('Fullscreen was refused. You can keep playing in this view.'); this._change(); };
    this._click = () => this.toggle();
    button.addEventListener('click', this._click);
    doc.addEventListener('fullscreenchange', this._change);
    doc.addEventListener('fullscreenerror', this._error);
    this.refresh();
  }
  get supported() { return typeof this.doc.documentElement.requestFullscreen === 'function' && this.doc.fullscreenEnabled !== false; }
  message(text) { this.status.textContent = text; }
  refresh() {
    const on = !!this.doc.fullscreenElement;
    this.button.textContent = on ? 'EXIT FULLSCREEN' : 'FULLSCREEN';
    this.button.setAttribute('aria-pressed', String(on));
    this.button.disabled = this.pending;
    this.button.title = this.supported ? 'Toggle fullscreen' : 'Fullscreen is unavailable in this browser';
    if (!this.supported) this.message('Fullscreen unavailable. The game remains playable.');
  }
  async toggle() {
    if (this.pending) return;
    if (!this.supported) { this.refresh(); return; }
    this.pending = true; this.refresh(); this.message('');
    try {
      if (this.doc.fullscreenElement) await this.doc.exitFullscreen();
      else await this.doc.documentElement.requestFullscreen({ navigationUI: 'hide' });
      this.message('');
    } catch {
      this.message('Fullscreen was refused. You can keep playing in this view.');
    } finally { this.pending = false; this._change(); }
  }
  dispose() {
    this.button.removeEventListener('click', this._click);
    this.doc.removeEventListener('fullscreenchange', this._change);
    this.doc.removeEventListener('fullscreenerror', this._error);
  }
}
