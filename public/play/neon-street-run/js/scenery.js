// Authored district landmarks, batched by material to keep mobile draw calls bounded.
import * as THREE from 'three';
import { registerGlow } from './timeofday.js';

export function buildDistrict(group, track, theme, quality, glows) {
  const district = theme.district || 'harbor';
  let seed = [...district].reduce((a, c) => a * 31 + c.charCodeAt(0), 7) >>> 0;
  const random = () => { seed = (1664525 * seed + 1013904223) >>> 0; return seed / 4294967296; };
  const material = (color, roughness = 0.8, metalness = 0.1) => new THREE.MeshStandardMaterial({ color, roughness, metalness });
  const concrete = material(0x596471), metal = material(0x253644, 0.45, 0.6), pale = material(0xd3d7cc);
  const glass = material(0x224956, 0.2, 0.7), wood = material(0x70584b), gold = material(0xe6a449, 0.5, 0.5);
  const colors = [material(0x287b89), material(0xb55543), material(0xd2b477), material(0x33495f)];
  const rock = [material(0x8d5947), material(0xb57654), material(0x613f39)];
  const glow = registerGlow(glows, new THREE.MeshStandardMaterial({ color: theme.accent, emissive: theme.accent, emissiveIntensity: 1.3 }), { floor: 0.16 });
  const warm = registerGlow(glows, new THREE.MeshStandardMaterial({ color: 0xffdca7, emissive: 0xffc078, emissiveIntensity: 1.2 }), { floor: 0.10 });
  const batches = new Map();
  const dummy = new THREE.Object3D();
  const addBox = (mat, x, y, z, w, h, d, angle = 0) => {
    if (!batches.has(mat)) batches.set(mat, []);
    dummy.position.set(x, y, z); dummy.rotation.set(0, angle, 0); dummy.scale.set(w, h, d); dummy.updateMatrix();
    batches.get(mat).push(dummy.matrix.clone());
  };
  const anchor = (fraction, offset, radius, inside = false) => {
    const i = Math.floor(fraction * track.waypoints.length) % track.waypoints.length;
    const e = inside ? track.innerPoint(i, offset) : track.edgePoint(i, offset);
    if (track.distanceTo(e.x, e.z) < track.width / 2 + radius + 3) return null;
    return { ...e, angle: track.poseAt(i).heading };
  };
  const local = (a, mat, x, y, z, w, h, d) => {
    const c = Math.cos(a.angle), s = Math.sin(a.angle);
    addBox(mat, a.x + x * c + z * s, y, a.z - x * s + z * c, w, h, d, a.angle);
  };
  const sign = (a, title, subtitle, width = 12, height = 4, y = 7) => {
    const canvas = document.createElement('canvas'); canvas.width = 768; canvas.height = 256;
    const ctx = canvas.getContext('2d'); ctx.fillStyle = '#0b1820'; ctx.fillRect(0, 0, 768, 256);
    ctx.fillStyle = '#' + theme.accent.toString(16).padStart(6, '0'); ctx.fillRect(0, 0, 12, 256);
    ctx.fillRect(38, 205, 692, 3); ctx.font = 'bold 65px Arial'; ctx.fillText(title, 38, 113, 680);
    ctx.fillStyle = '#bdcdd3'; ctx.font = '22px Arial'; ctx.fillText(subtitle, 40, 167, 680);
    const tex = new THREE.CanvasTexture(canvas); tex.colorSpace = THREE.SRGBColorSpace;
    const mat = registerGlow(glows, new THREE.MeshStandardMaterial({ map: tex, emissiveMap: tex, emissive: 0xffffff, emissiveIntensity: 0.7, side: THREE.DoubleSide }), { floor: 0.2 });
    const board = new THREE.Mesh(new THREE.PlaneGeometry(width, height), mat);
    board.position.set(a.x, y, a.z); board.rotation.y = a.angle; group.add(board);
  };

  // Human-scale frontage gives the road a street edge instead of isolated towers.
  if (district !== 'canyon' && district !== 'airport') {
    const count = quality.id === 'low' ? 12 : 22;
    for (let i = 0; i < count; i++) {
      const a = anchor((i + 0.3) / count, track.width / 2 + 17, 10);
      if (!a) continue;
      a.angle += Math.PI / 2;
      const h = 5 + random() * 5;
      local(a, colors[i % 4], 0, h / 2, 0, 13, h, 7);
      local(a, concrete, 0, 0.22, 1, 15, 0.44, 11);
      local(a, metal, 0, h + 0.18, 0, 13.6, 0.36, 7.6);
      for (const x of [-4, 0, 4]) {
        local(a, glass, x, 2.05, 3.55, 3.2, 3.1, 0.08);
        local(a, warm, x, 3.7, 3.60, 3.1, 0.09, 0.10);
      }
      local(a, glow, 0, 4.5, 3.7, 11, 0.10, 0.20);
      local(a, metal, 0, 4.1, 4.1, 13, 0.20, 1.2);
      if (i % 5 === 0) {
        const s = Math.sin(a.angle), c = Math.cos(a.angle);
        sign({x: a.x + s * 3.72, z: a.z + c * 3.72, angle: a.angle}, ['AFTER HOURS', 'MOTOR WORKS', 'PORT / 09', 'NIGHT MARKET', 'FUEL & CO.'][i / 5 % 5], 'OPEN LATE  /  STREET DIVISION', 9, 2.5, h - 1);
      }
    }
  }
  if (district === 'harbor') {
    for (let i = 0; i < 9; i++) {
      const a = anchor(i / 9 + 0.03, track.width / 2 + 45, 22, i % 2 === 0);
      if (!a) continue;
      local(a, concrete, 0, 0.14, 0, 30, 0.28, 30);
      for (let j = 0; j < 5; j++) {
        const x = (j % 3 - 1) * 6, y = j > 2 ? 5.5 : 1.9, z = j > 2 ? 0 : 5;
        local(a, colors[(i + j) % 4], x, y, z, 5.6, 3.6, 12);
        for (let rib = -5; rib <= 5; rib += 1) local(a, metal, x + 2.83, y, z + rib, 0.08, 3.45, 0.10);
      }
      if (i % 3 === 0) {
        for (const x of [-10, 10]) local(a, gold, x, 16, 0, 1.2, 32, 1.6);
        local(a, gold, 0, 32, 1, 25, 1.6, 3);
        local(a, gold, 0, 34, 1, 1.6, 1.2, 34);
        local(a, metal, 0, 25, 12, 0.10, 18, 0.10);
        local(a, gold, 0, 16, 12, 5, 0.4, 2);
        local(a, glass, 3, 30, 1, 3.5, 3, 3.5);
      }
    }
  } else if (district === 'canyon') {
    const geometry = new THREE.CylinderGeometry(0.65, 1, 1, 7, 1);
    for (let i = 0; i < 58; i++) {
      const a = anchor(i / 58, track.width / 2 + 24 + random() * 30, 17);
      if (!a) continue;
      const height = 16 + random() * 45, width = 9 + random() * 8;
      for (let layer = 0; layer < 3; layer++) {
        const m = new THREE.Mesh(geometry, rock[layer]);
        m.position.set(a.x, height * (layer + 0.5) / 3, a.z); m.rotation.y = i * 0.8;
        m.scale.set(width * (1 - layer * 0.16), height / 3, width * (1 - layer * 0.12));
        m.castShadow = true; m.receiveShadow = true; group.add(m);
      }
    }
    const a = anchor(0.1, track.width / 2 + 25, 12);
    if (a) { a.angle += Math.PI / 2; sign(a, 'RED ROCK PASS', 'ELEV. 2048 M   /   SECTOR 03', 17, 5, 8); }
  } else if (district === 'airport') {
    for (let i = 0; i < 6; i++) {
      const a = anchor((i + 0.4) / 6, track.width / 2 + 50, 28);
      if (!a) continue;
      local(a, pale, 0, 6, 0, 36, 12, 25);
      local(a, metal, 0, 5, 12.6, 28, 9, 0.2);
      local(a, glow, 0, 10, 12.8, 29, 0.12, 0.3);
      local(a, concrete, 0, 0.12, 18, 42, 0.24, 15);
      for (let k = -3; k <= 3; k++) local(a, pale, k * 4, 5, 12.8, 0.10, 8, 0.1);
    }
    const a = { x: 0, z: 0, angle: 0 };
    local(a, concrete, 0, 17, 0, 7, 34, 7);
    local(a, pale, 0, 32, 0, 15, 2, 12);
    local(a, glass, 0, 35, 0, 13, 4, 10);
    local(a, metal, 0, 37.5, 0, 16, 1, 13);
    local(a, glow, 0, 38.1, 0, 16, 0.15, 13);
    local(a, metal, 0, 43, 0, 0.25, 10, 0.25);
    for (let i = -8; i <= 8; i++) {
      addBox(pale, i * 10, 0.08, 25, 5, 0.05, 0.7);
      addBox(glow, i * 10, 0.3, 31, 0.4, 0.5, 0.4);
    }
  } else if (district === 'marina') {
    // Piers and small yachts sit within the central water, safely inside the circuit.
    for (let i = -2; i <= 2; i++) {
      addBox(wood, i * 17, 0.45, 0, 3, 0.6, 48);
      for (let j = -1; j <= 1; j++) {
        const x = i * 17 + 6, z = j * 14;
        addBox(pale, x, 0.65, z, 5, 1.2, 10);
        addBox(glass, x, 1.6, z - 1, 3.4, 1.2, 4.6);
        addBox(pale, x, 2.3, z - 1, 3.8, 0.2, 5);
        addBox(metal, x, 5.5, z, 0.08, 9, 0.08);
      }
    }
    // A cable-stayed bridge beyond the course becomes a clear horizon landmark.
    const z = -track.extent.z - 100;
    addBox(concrete, 0, 12, z, 280, 2, 12);
    for (const x of [-65, 65]) {
      addBox(pale, x, 37, z, 3, 74, 4);
      for (const side of [-1, 1]) for (let j = 1; j <= 5; j++) {
        const start = new THREE.Vector3(x, 68, z), end = new THREE.Vector3(x + side * j * 12, 13, z);
        const delta = end.clone().sub(start);
        const cable = new THREE.Mesh(new THREE.CylinderGeometry(0.15, 0.15, delta.length(), 4), pale);
        cable.position.copy(start.add(end).multiplyScalar(0.5)); cable.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), delta.normalize()); group.add(cable);
      }
    }
  } else if (district === 'downtown') {
    // Stepped signature tower with a floating illuminated crown.
    for (let i = 0; i < 5; i++) {
      addBox(glass, 0, 14 + i * 24, 0, 30 - i * 4, 28, 30 - i * 4);
      addBox(glow, 0, 28 + i * 24, 0, 30.5 - i * 4, 0.35, 30.5 - i * 4);
    }
    addBox(metal, 0, 141, 0, 0.8, 45, 0.8);
    addBox(pale, 0, 1, 0, 60, 2, 60);
  }
  // Each material has one draw call, regardless of how many facade ribs it contains.
  const geo = new THREE.BoxGeometry(1, 1, 1);
  for (const [mat, matrices] of batches) {
    const inst = new THREE.InstancedMesh(geo, mat, matrices.length);
    matrices.forEach((m, i) => inst.setMatrixAt(i, m));
    inst.castShadow = true; inst.receiveShadow = true; inst.instanceMatrix.needsUpdate = true;
    group.add(inst);
  }
  // Unused palette entries never reach the scene's disposal traversal.
  for (const mat of [concrete, metal, pale, glass, wood, gold, ...colors, ...rock, glow, warm]) {
    if (!batches.has(mat) && !group.getObjectsByProperty('material', mat).length) mat.dispose();
  }
}
