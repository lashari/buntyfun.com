// The car: physics state, a per-car gearbox, and procedural geometry built from a spec.
//
// Nothing here is hard-coded to a particular vehicle. `spec.body` drives the silhouette
// (profile, dimensions, ride height, wing and wheel style) and `spec` drives the gearbox,
// while physics.js reads the same spec for grip/steer/accel. Customisation (paint, accent,
// wheels, underglow) is layered on top as plain data, so visual changes can never alter
// how a car drives.

import * as THREE from 'three';
import { gearsFor, DEFAULT_SPEC } from './cars.js';
import { damp } from './utils.js';
import { buildVehicle } from './vehicle-design.js';

export class Car {
  constructor(opts = {}) {
    this.spec = opts.spec || DEFAULT_SPEC;
    this.style = opts.style || {};
    this.isPlayer = false;

    this.group = new THREE.Group();
    this.group.rotation.order = 'YXZ';
    this.body = new THREE.Group();   // leans / squats independently of the heading
    this.group.add(this.body);
    this.position = this.group.position;

    this.gears = gearsFor(this.spec.topSpeedKmh, this.spec.accelScale);

    // physics state
    this.heading = 0;
    this.vel = { x: 0, z: 0 };
    this.steer = 0;
    this.yawRate = 0;
    this.speed = 0; this.speedKmh = 0; this.fwdSpeed = 0; this.latSpeed = 0;
    this.slipAngle = 0; this.drifting = false; this.driftHard = false;
    this.gear = 0;
    this.nos = 100; this.nosActive = false;
    this.surfaceGrip = 1;
    this.nightLevel = 1;      // 1 = night: lights and underglow at full strength
    this._braking = false;
    this._roll = 0; this._pitch = 0;

    this._frontPivots = [];
    this._wheels = [];
    this._rims = [];
    this._disposables = [];
    this._spawn = { x: 0, z: 0, heading: 0 };
    this._build();
  }

  _build() { buildVehicle(this); }

  setSpawn(x, z, heading = 0) { this._spawn = { x, z, heading }; this.reset(); }

  reset() {
    this.position.set(this._spawn.x, 0, this._spawn.z);
    this.heading = this._spawn.heading;
    this.vel.x = 0; this.vel.z = 0;
    this.steer = 0; this.yawRate = 0;
    this.speed = 0; this.speedKmh = 0; this.fwdSpeed = 0; this.latSpeed = 0;
    this.slipAngle = 0; this.drifting = false; this.driftHard = false;
    this.gear = 0; this.nos = 100; this.nosActive = false;
    this.surfaceGrip = 1;
    this._roll = 0; this._pitch = 0;
    this.syncVisuals(0);
  }

  // --- gearbox (per-car table) ---
  shiftUp() {
    if (this.gear >= this.gears.length - 1) return false;
    if (this.speedKmh >= this.gears[this.gear].up) { this.gear++; return true; }
    return false;
  }
  shiftDown() { if (this.gear > 0) { this.gear--; return true; } return false; }
  canShiftUp() { return this.gear < this.gears.length - 1 && this.speedKmh >= this.gears[this.gear].up; }
  canShiftDown() { return this.gear > 0 && this.speedKmh < this.gears[this.gear - 1].up * 0.6; }

  // Rev fraction within the current gear — drives the engine note and any tacho.
  rev() {
    const g = this.gear;
    const floor = g === 0 ? 0 : this.gears[g - 1].max;
    const ceil = this.gears[g].max;
    return Math.max(0, Math.min(1, (this.speedKmh - floor) / (ceil - floor || 1)));
  }

  syncVisuals(dt) {
    this.group.rotation.y = this.heading;

    // body roll into the slide, dive under braking, squat on nitrous
    const rollTarget = -(this.latSpeed / 14) * 0.1;
    const pitchTarget = this._braking && this.fwdSpeed > 2 ? -0.025 : (this.nosActive ? 0.022 : 0);
    if (dt > 0) {
      this._roll = damp(this._roll, rollTarget, 7, dt);
      this._pitch = damp(this._pitch, pitchTarget, 6, dt);
    } else { this._roll = rollTarget; this._pitch = pitchTarget; }
    this.body.rotation.z = this._roll;
    this.body.rotation.x = this._pitch;

    for (const pivot of this._frontPivots) pivot.rotation.y = this.steer;
    const spin = (this.fwdSpeed * dt) / this._wheelR;
    for (const w of this._wheels) w.rotation.x -= spin;
    for (const r of this._rims) r.rotation.x -= spin;

    // Lights read differently by time of day: tail lamps stay legible under braking even
    // in sunlight (they are a gameplay signal), while the decorative underglow all but
    // disappears. `nightLevel` is 1 at night and ~0.18 at midday.
    const n = this.nightLevel;
    const braking = this._braking && this.fwdSpeed > 1;
    this.tailMat.emissiveIntensity = braking ? 2.8 : (0.25 + 0.85 * n);
    this.headMat.emissiveIntensity = 0.3 + 1.3 * n;
    if (this.glowMat) {
      const base = this.nosActive ? 0.7 : (this.drifting ? 0.5 : 0.35);
      this.glowMat.opacity = base * (0.08 + 0.92 * n);
    }
  }

  // Release the geometry and materials this car created (called when swapping cars/maps).
  dispose() {
    for (const d of this._disposables) d.dispose?.();
    this._disposables.length = 0;
  }
}
