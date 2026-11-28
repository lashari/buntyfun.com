// Third-person chase camera: smooth position + look-at follow with slight lag,
// speed-based FOV, and collision-shake support. Deliberately not rigidly parented
// to the car so it lags and leans a touch, which reads as "speed".

import * as THREE from 'three';
import { damp, clamp } from './utils.js';
import { CAR } from './physics.js';

export const CAMERA_MODES = [
  { id: 'far', name: 'FAR CHASE', back: 9, height: 4.2, ahead: 7, lookHeight: 1.3 },
  { id: 'close', name: 'CLOSE CHASE', back: 6.3, height: 2.7, ahead: 8, lookHeight: 1.15 },
  { id: 'cockpit', name: 'COCKPIT / DRIVER', back: 6.3, height: 2.7, ahead: 8, lookHeight: 1.15 },
];

export class ChaseCamera {
  constructor(aspect, mode = 'far') {
    this.baseFov = 62;
    this.maxFov = 76;
    this.cam = new THREE.PerspectiveCamera(this.baseFov, aspect, 0.1, 3000);
    this._pos = new THREE.Vector3(0, 5, -12);
    this._look = new THREE.Vector3();
    this.shake = 0;
    this.mode = CAMERA_MODES.some(m => m.id === mode) ? mode : 'far';
    const preset = CAMERA_MODES.find(m => m.id === this.mode);
    this._back = preset.back; this._height = preset.height;
    this._ahead = preset.ahead; this._lookHeight = preset.lookHeight;
    this._driverBlend = this.mode === 'cockpit' ? 1 : 0;
    this._initialized = false;
    this._eye = new THREE.Vector3(); this._target = new THREE.Vector3();
    this._resources = [];
    this._buildInterior();
  }

  setMode(id) {
    if (!CAMERA_MODES.some(m => m.id === id)) return false;
    this.mode = id; return true;
  }

  get hideCarBody() { return this._driverBlend > 0.70; }

  _buildInterior() {
    // Camera-local approximation: external coachwork is unchanged. Negative Z is
    // forward in camera space, so the dashboard remains below the road sightline.
    const group = new THREE.Group(); this.cam.add(group); this.interior = group;
    const material = new THREE.MeshStandardMaterial({ color: 0x101921, roughness: 0.8 });
    const trim = new THREE.MeshBasicMaterial({ color: 0x22e3ff });
    this._resources.push(material, trim);
    const add = (geo, mat, x, y, z) => {
      this._resources.push(geo); const m = new THREE.Mesh(geo, mat); m.position.set(x, y, z); group.add(m); return m;
    };
    add(new THREE.BoxGeometry(1.5, 0.17, 0.38), material, 0, -0.43, -0.73);
    add(new THREE.BoxGeometry(0.65, 0.008, 0.014), trim, -0.14, -0.33, -0.53);
    this.driverWheel = add(new THREE.TorusGeometry(0.16, 0.016, 6, 24), material, -0.16, -0.32, -0.48);
    const hub = add(new THREE.BoxGeometry(0.22, 0.035, 0.025), material, -0.16, -0.32, -0.48);
    this.driverWheel.add(hub); group.remove(hub); hub.position.set(0, 0, 0);
    for (const side of [-1, 1]) {
      const pillar = add(new THREE.BoxGeometry(0.025, 0.55, 0.04), material, side * 0.68, -0.06, -0.77);
      pillar.rotation.z = side * -0.22;
    }
    group.visible = false;
  }

  resize(aspect) { this.cam.aspect = aspect; this.cam.updateProjectionMatrix(); }

  // amount ~ 0..1 collision intensity
  addShake(amount) { this.shake = Math.min(this.shake + amount, 1.4); }

  update(car, dt, nos = false) {
    const fx = Math.sin(car.heading), fz = Math.cos(car.heading);
    const sx = Math.cos(car.heading), sz = -Math.sin(car.heading);
    const preset = CAMERA_MODES.find(m => m.id === this.mode);
    for (const [field, target] of [['_back', preset.back], ['_height', preset.height], ['_ahead', preset.ahead], ['_lookHeight', preset.lookHeight]]) {
      this[field] = damp(this[field], target, 6, dt);
    }
    this._driverBlend = damp(this._driverBlend, this.mode === 'cockpit' ? 1 : 0, 6, dt);
    if (this._driverBlend > 0.999) this._driverBlend = 1;
    if (this._driverBlend < 0.001) this._driverBlend = 0;
    const back = this._back, height = this._height, ahead = this._ahead;

    // desired position: behind + above the car, pushed slightly to the outside of a
    // slide so a drift reads from the camera as well as the car
    const slide = clamp((car.latSpeed || 0) / 12, -1, 1);
    const dx = car.position.x - fx * back - sx * slide * 2.2;
    const dz = car.position.z - fz * back - sz * slide * 2.2;
    const follow = 6; // responsiveness (lower = more lag)
    if (!this._initialized) {
      this._pos.set(dx, height, dz);
      this._look.set(car.position.x + fx * ahead, this._lookHeight, car.position.z + fz * ahead);
      this._initialized = true;
    }
    this._pos.x = damp(this._pos.x, dx, follow, dt);
    this._pos.y = damp(this._pos.y, height, follow, dt);
    this._pos.z = damp(this._pos.z, dz, follow, dt);
    if (this._pos.y < 1.3) this._pos.y = 1.3; // never clip through ground

    // collision shake
    let shx = 0, shy = 0;
    if (this.shake > 0.001) {
      shx = (Math.random() - 0.5) * this.shake;
      shy = (Math.random() - 0.5) * this.shake;
      this.shake = damp(this.shake, 0, 7, dt);
    }
    // The settled driver camera is rigidly attached to the seat rather than lagging
    // outside the car during a fast turn. Reduce collision shake to cabin scale.
    const seatY = Math.max(1.10, (car.spec?.body?.rideHeight ?? 0.52) + 0.36 + (car.spec?.body?.cabinHeight ?? 0.44) * 0.70);
    this._eye.set(car.position.x + sx * 0.30 + fx * 0.05, car.position.y + seatY, car.position.z + sz * 0.30 + fz * 0.05);
    this.cam.position.copy(this._pos).lerp(this._eye, this._driverBlend);
    const shakeScale = 1 - this._driverBlend * 0.88;
    this.cam.position.x += shx * shakeScale;
    this.cam.position.y = Math.max(1.05, this.cam.position.y + shy * shakeScale);

    // look a little ahead of the car, biased into the direction it's actually travelling
    this._look.x = damp(this._look.x, car.position.x + fx * ahead + sx * slide * 3.2, follow, dt);
    this._look.y = damp(this._look.y, this._lookHeight, follow, dt);
    this._look.z = damp(this._look.z, car.position.z + fz * ahead + sz * slide * 3.2, follow, dt);
    this._target.set(this._eye.x + fx * 30 + sx * slide * 0.5, this._eye.y, this._eye.z + fz * 30 + sz * slide * 0.5);
    this._target.lerpVectors(this._look, this._target, this._driverBlend);
    this.cam.lookAt(this._target);
    this.interior.visible = this._driverBlend > 0.96;
    this.driverWheel.rotation.z = -(car.steer || 0) * 2.2;

    // speed-based FOV, with an extra punch on nitrous
    const speedNorm = clamp(car.speed / CAR.maxSpeed, 0, 1);
    let targetFov = this.baseFov + (this.maxFov - this.baseFov) * speedNorm;
    if (nos) targetFov += 7;
    targetFov += this._driverBlend * 6;
    this.cam.fov = damp(this.cam.fov, targetFov, nos ? 9 : 4, dt);
    this.cam.updateProjectionMatrix();
  }

  dispose() { this._resources.forEach(r => r.dispose()); this._resources.length = 0; }
}
