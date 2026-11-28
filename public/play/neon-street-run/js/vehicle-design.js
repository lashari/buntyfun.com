// Procedural coachwork: metres, +Z forward. Visual geometry never enters physics.
import * as THREE from 'three';
import { WHEELS } from './cars.js';

export function buildVehicle(car) {
  const b = car.spec.body || {}, style = car.style;
  const L = b.length ?? 4.5, W = b.width ?? 1.94, Y = b.rideHeight ?? 0.52;
  const R = b.wheelR ?? 0.46, tw = b.trackWidth ?? 1.02;
  const profile = b.profile || 'coupe', wedge = profile === 'wedge', muscle = profile === 'muscle', hatch = profile === 'hatch';
  const wheelStyle = WHEELS.find(w => w.id === style.wheels) || WHEELS[0];
  const own = x => { car._disposables.push(x); return x; };
  const mat = (color, roughness = 0.4, metalness = 0.2) => own(new THREE.MeshStandardMaterial({ color, roughness, metalness }));
  const paint = own(new THREE.MeshPhysicalMaterial({ color: style.paint ?? car.spec.paint, metalness: 0.48, roughness: 0.26, clearcoat: 1, clearcoatRoughness: 0.16 }));
  const dark = mat(0x10151c, 0.64), rubber = mat(0x12151a, 0.9), trim = mat(0x303b48, 0.35, 0.7);
  const glass = own(new THREE.MeshPhysicalMaterial({ color: 0x183543, metalness: 0.38, roughness: 0.12, clearcoat: 1 }));
  const alloy = mat(wheelStyle.rim, 0.25, 0.85), brake = mat(0xe94032, 0.5);
  const accent = mat(style.accent ?? car.spec.accent ?? 0x22e3ff, 0.35, 0.5);
  const mesh = (geo, material, parent = car.body) => {
    const m = new THREE.Mesh(own(geo), material); m.castShadow = true; m.receiveShadow = true; parent.add(m); return m;
  };
  const box = (material, w, h, d, x, y, z, parent = car.body) => {
    const m = mesh(new THREE.BoxGeometry(w, h, d), material, parent); m.position.set(x, y, z); return m;
  };
  const panel = (points, material) => {
    const g = new THREE.BufferGeometry();
    g.setAttribute('position', new THREE.Float32BufferAttribute(points.flat(), 3));
    g.setIndex([0, 1, 2, 0, 2, 3]); g.computeVertexNormals();
    material.side = THREE.DoubleSide;
    return mesh(g, material);
  };
  const beam = (a, b, radius, material) => {
    const start = new THREE.Vector3(...a), end = new THREE.Vector3(...b), delta = end.clone().sub(start);
    const m = mesh(new THREE.CylinderGeometry(radius, radius, delta.length(), 6), material);
    m.position.copy(start.add(end).multiplyScalar(0.5)); m.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), delta.normalize()); return m;
  };

  // A continuous shell with narrowed nose, raised shoulders and real wheel openings.
  const axles = [-tw * 1.47, tw * 1.45], positions = [], indices = [], sections = 72;
  for (let i = 0; i <= sections; i++) {
    const z = -L / 2 + L * i / sections;
    const end = Math.pow(Math.abs(z / (L / 2)), 5);
    const width = W / 2 * (1 - end * (wedge ? 0.19 : 0.10));
    const front = Math.max(0, z / (L / 2));
    let top = Y + 0.38 - front * front * (wedge ? 0.24 : muscle ? 0.06 : 0.13);
    let bottom = Y - 0.26;
    for (const axle of axles) {
      const dz = Math.abs(z - axle), radius = R + 0.065;
      if (dz < radius) bottom = Math.max(bottom, R + Math.sqrt(radius * radius - dz * dz));
      top = Math.max(top, R + 0.17 + Math.sqrt(Math.max(0, (R + 0.1) ** 2 - dz * dz)) * 0.82);
    }
    const flare = Math.max(...axles.map(a => Math.exp(-(((z - a) / 0.63) ** 2)))) * 0.11;
    const x = width + flare;
    const ring = [[-x, bottom], [-x, top - 0.08], [-width * 0.76, top], [width * 0.76, top], [x, top - 0.08], [x, bottom]];
    for (const [rx, ry] of ring) positions.push(rx, Math.max(ry, bottom), z);
    if (i < sections) for (let j = 0; j < 5; j++) {
      const a = i * 6 + j, c = a + 6; indices.push(a, c, a + 1, c, c + 1, a + 1);
    }
  }
  const shell = new THREE.BufferGeometry(); shell.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
  shell.setIndex(indices); shell.computeVertexNormals(); mesh(shell, paint);
  box(dark, W * 0.74, 0.14, L * 0.91, 0, Y - 0.2, 0);
  for (const side of [-1, 1]) {
    box(dark, 0.14, 0.16, L * 0.40, side * W * 0.51, Y - 0.21, 0);
    box(accent, 0.025, 0.032, L * 0.39, side * W * 0.55, Y - 0.13, 0);
    // Rolled fender lips follow the tyre opening, instead of hiding it with a block.
    for (const z of axles) {
      const lip = mesh(new THREE.TorusGeometry(R + 0.08, 0.04, 6, 24, Math.PI), paint);
      lip.rotation.y = Math.PI / 2; lip.position.set(side * (W / 2 + 0.10), R, z);
    }
    box(dark, 0.025, 0.11, 0.38, side * W * 0.507, Y + 0.08, -0.59);
    box(trim, 0.028, 0.028, 0.2, side * W * 0.514, Y + 0.20, -0.39);
  }

  // Raked glazing and individually framed side windows; each profile has its own roof.
  const base = Y + 0.36, roof = base + (b.cabinHeight ?? 0.44) + 0.08;
  const cb = hatch ? -1.45 : muscle ? -1.20 : -1.30;
  const cf = wedge ? 0.94 : hatch ? 0.80 : 0.73;
  const rb = hatch ? -1.20 : muscle ? -0.88 : -0.82;
  const rf = wedge ? 0.22 : muscle ? 0.10 : 0.16;
  const bw = W * 0.41, rw = W * (wedge ? 0.31 : 0.34);
  panel([[-bw, base, cf], [bw, base, cf], [rw, roof, rf], [-rw, roof, rf]], glass);
  panel([[bw, base, cb], [-bw, base, cb], [-rw, roof, rb], [rw, roof, rb]], glass);
  panel([[-rw, roof + 0.012, rb], [-rw, roof + 0.012, rf], [rw, roof + 0.012, rf], [rw, roof + 0.012, rb]], paint);
  for (const s of [-1, 1]) {
    panel([[s * bw, base, cb], [s * bw, base, cf], [s * rw, roof, rf], [s * rw, roof, rb]], glass);
    for (const [bz, rz] of [[cb, rb], [cf, rf]]) beam([s * bw, base, bz], [s * rw, roof, rz], 0.043, paint);
    beam([s * bw, base, -0.52], [s * rw, roof, -0.52], 0.035, dark);
    beam([s * bw, base, cb], [s * bw, base, cf], 0.026, trim);
    beam([s * rw, roof, rb], [s * rw, roof, rf], 0.025, paint);
    box(dark, 0.18, 0.04, 0.06, s * W * 0.47, base + 0.1, cf - 0.08);
    box(paint, 0.21, 0.12, 0.24, s * W * 0.55, base + 0.14, cf - 0.10);
    box(glass, 0.17, 0.075, 0.012, s * W * 0.55, base + 0.14, cf - 0.225);
  }
  // Sculpted front fascia, recessed intakes, thin running lights and rear diffuser.
  const frontY = Y + (wedge ? 0.10 : 0.23);
  box(paint, W * 0.89, 0.34, 0.18, 0, frontY - 0.12, L / 2 - 0.04);
  box(dark, W * 0.94, 0.08, 0.38, 0, Y - 0.23, L / 2 - 0.06);
  box(dark, W * 0.44, 0.16, 0.025, 0, frontY - 0.15, L / 2 + 0.057);
  for (const s of [-1, 1]) {
    box(dark, W * 0.21, 0.13, 0.025, s * W * 0.33, frontY - 0.15, L / 2 + 0.058);
    if (b.canards) box(dark, 0.20, 0.04, 0.30, s * W * 0.46, frontY - 0.21, L / 2);
    for (let i = 0; i < 3; i++) box(trim, W * 0.18, 0.012, 0.03, s * W * 0.33, frontY - 0.19 + i * 0.045, L / 2 + 0.07);
  }
  car.headMat = own(new THREE.MeshStandardMaterial({ color: 0xeeffff, emissive: 0xbceeff, emissiveIntensity: 1.6 }));
  car.tailMat = own(new THREE.MeshStandardMaterial({ color: 0xff1838, emissive: 0xff0528, emissiveIntensity: 1.1 }));
  for (const s of [-1, 1]) {
    box(dark, W * 0.30, 0.12, 0.07, s * W * 0.29, frontY + 0.025, L / 2 + 0.045);
    box(car.headMat, W * 0.26, 0.035, 0.025, s * W * 0.29, frontY + 0.052, L / 2 + 0.09);
    box(car.headMat, 0.035, 0.073, 0.026, s * W * 0.41, frontY + 0.015, L / 2 + 0.091);
  }
  box(paint, W * 0.90, 0.38, 0.15, 0, Y + 0.12, -L / 2 + 0.025);
  box(dark, W * 0.87, 0.15, 0.045, 0, Y + 0.20, -L / 2 - 0.055);
  box(car.tailMat, W * 0.80, 0.034, 0.028, 0, Y + 0.24, -L / 2 - 0.083);
  for (const s of [-1, 1]) for (let i = 0; i < (muscle ? 3 : 2); i++) {
    box(car.tailMat, 0.06, 0.075, 0.03, s * (W * 0.33 - i * 0.1), Y + 0.20, -L / 2 - 0.085);
  }
  box(dark, W * 0.87, 0.18, 0.35, 0, Y - 0.17, -L / 2 + 0.03);
  for (let i = -2; i <= 2; i++) box(trim, 0.032, 0.18, 0.42, i * 0.24, Y - 0.18, -L / 2 + 0.03);
  box(trim, 0.38, 0.12, 0.018, 0, Y + 0.015, -L / 2 - 0.065);
  for (const s of [-1, 1]) {
    const pipe = mesh(new THREE.TorusGeometry(0.085, 0.018, 8, 16), alloy);
    pipe.position.set(s * W * 0.34, Y - 0.12, -L / 2 - 0.17);
    const hole = mesh(new THREE.CircleGeometry(0.075, 16), dark); hole.rotation.y = Math.PI;
    hole.position.copy(pipe.position); hole.position.z += 0.012;
  }
  if (b.scoop) {
    box(dark, W * 0.30, 0.07, 0.6, 0, Y + 0.37, 1.10);
    for (let i = 0; i < 3; i++) box(trim, W * 0.27, 0.022, 0.035, 0, Y + 0.414, 0.91 + i * 0.17);
  }
  if (muscle) for (const s of [-1, 1]) box(dark, 0.18, 0.014, 1.02, s * 0.22, Y + 0.34, 1.30);
  if (b.wing === 'tall') {
    for (const s of [-1, 1]) box(dark, 0.07, 0.39, 0.18, s * W * 0.33, Y + 0.51, -L / 2 + 0.27);
    box(dark, W * 1.04, 0.065, 0.40, 0, Y + 0.73, -L / 2 + 0.21);
    box(accent, W * 1.04, 0.025, 0.035, 0, Y + 0.74, -L / 2 + 0.015);
    for (const s of [-1, 1]) box(dark, 0.045, 0.21, 0.45, s * W * 0.52, Y + 0.77, -L / 2 + 0.21);
  } else {
    const lip = box(paint, W * 0.88, 0.10, 0.25, 0, Y + 0.37, -L / 2 + 0.10); lip.rotation.x = -0.24;
  }

  // The rotating wheel is a group: spokes, rim and sidewall rotate about its X axle.
  // Brakes belong to the steering pivot so calipers do not rotate with the wheel.
  for (const x of [-tw, tw]) for (const z of axles) {
    const s = Math.sign(x), pivot = new THREE.Group(), rolling = new THREE.Group();
    pivot.position.set(x, R, z); car.body.add(pivot); pivot.add(rolling);
    const tyre = mesh(new THREE.CylinderGeometry(R, R, 0.32, 32, 1), rubber, rolling); tyre.rotation.z = Math.PI / 2;
    const rotor = mesh(new THREE.CylinderGeometry(R * 0.66, R * 0.66, 0.02, 24), trim, pivot);
    rotor.rotation.z = Math.PI / 2; rotor.position.x = s * 0.16;
    box(brake, 0.065, R * 0.62, R * 0.22, s * 0.18, 0, -R * 0.49, pivot);
    for (const side of [-1, 1]) {
      const rim = mesh(new THREE.TorusGeometry(R * 0.74, 0.028, 8, 32), alloy, rolling);
      rim.rotation.y = Math.PI / 2; rim.position.x = side * 0.17;
      const sidewall = mesh(new THREE.TorusGeometry(R * 0.90, 0.018, 6, 32), rubber, rolling);
      sidewall.rotation.y = Math.PI / 2; sidewall.position.x = side * 0.162;
    }
    for (let i = 0; i < wheelStyle.spokes; i++) {
      const a = i / wheelStyle.spokes * Math.PI * 2;
      const spoke = box(alloy, 0.038, R * 0.62, 0.052, s * 0.185, Math.cos(a) * R * 0.37, Math.sin(a) * R * 0.37, rolling);
      spoke.rotation.x = a;
    }
    const hub = mesh(new THREE.CylinderGeometry(R * 0.16, R * 0.16, 0.045, 12), alloy, rolling);
    hub.rotation.z = Math.PI / 2; hub.position.x = s * 0.19;
    car._wheels.push(rolling);
    batchMeshes(rolling, own);
    if (z > 0) car._frontPivots.push(pivot);
  }
  car._wheelR = R;
  batchMeshes(car.body, own);

  // Soft contact shadow and underbody light avoid a hard floating rectangle.
  const c = document.createElement('canvas'); c.width = c.height = 64;
  const ctx = c.getContext('2d'), gradient = ctx.createRadialGradient(32, 32, 6, 32, 32, 32);
  gradient.addColorStop(0, '#fff'); gradient.addColorStop(0.45, '#ffffffaa'); gradient.addColorStop(1, '#ffffff00');
  ctx.fillStyle = gradient; ctx.fillRect(0, 0, 64, 64); const texture = own(new THREE.CanvasTexture(c));
  const shadow = own(new THREE.MeshBasicMaterial({ map: texture, color: 0x000000, transparent: true, opacity: 0.62, depthWrite: false }));
  const contact = mesh(new THREE.PlaneGeometry(W * 1.6, L * 1.26), shadow, car.group);
  contact.rotation.x = -Math.PI / 2; contact.position.y = 0.045; contact.castShadow = false;
  if (style.glow !== 0 && style.glow !== undefined) {
    car.glowMat = own(new THREE.MeshBasicMaterial({ map: texture, color: style.glow, transparent: true, opacity: 0.35, blending: THREE.AdditiveBlending, depthWrite: false }));
    const glow = mesh(new THREE.PlaneGeometry(W * 1.8, L * 1.25), car.glowMat, car.group);
    glow.rotation.x = -Math.PI / 2; glow.position.y = 0.052; glow.castShadow = false;
  }
}

// Merge stationary pieces by material; steering and rotating groups stay independent.
function batchMeshes(group, own) {
  const batches = new Map();
  for (const child of [...group.children]) {
    if (!child.isMesh) continue;
    child.updateMatrix();
    const copy = child.geometry.clone().applyMatrix4(child.matrix);
    const g = copy.index ? copy.toNonIndexed() : copy;
    if (g !== copy) copy.dispose();
    if (!batches.has(child.material)) batches.set(child.material, []);
    batches.get(child.material).push(g);
    group.remove(child);
  }
  for (const [material, geometries] of batches) {
    const merged = new THREE.BufferGeometry();
    for (const name of ['position', 'normal', 'uv']) {
      if (!geometries.every(g => g.getAttribute(name))) continue;
      const count = geometries.reduce((n, g) => n + g.getAttribute(name).array.length, 0);
      const values = new Float32Array(count); let offset = 0;
      for (const g of geometries) { const a = g.getAttribute(name).array; values.set(a, offset); offset += a.length; }
      merged.setAttribute(name, new THREE.BufferAttribute(values, geometries[0].getAttribute(name).itemSize));
    }
    geometries.forEach(g => g.dispose());
    const mesh = new THREE.Mesh(own(merged), material); mesh.castShadow = true; mesh.receiveShadow = true; group.add(mesh);
  }
}
