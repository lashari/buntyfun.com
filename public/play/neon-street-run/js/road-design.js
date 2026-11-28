import * as THREE from 'three';
import { registerGlow } from './timeofday.js';

export function asphaltTexture() {
  const c = document.createElement('canvas'); c.width = c.height = 256;
  const ctx = c.getContext('2d'), pixels = ctx.createImageData(256, 256);
  let seed = 8192;
  for (let i = 0; i < pixels.data.length; i += 4) {
    seed = (1664525 * seed + 1013904223) >>> 0;
    const v = 65 + (seed >>> 27);
    pixels.data[i] = v; pixels.data[i + 1] = v + 3; pixels.data[i + 2] = v + 6; pixels.data[i + 3] = 255;
  }
  ctx.putImageData(pixels, 0, 0);
  ctx.strokeStyle = '#202833'; ctx.lineWidth = 1.5;
  ctx.beginPath(); ctx.moveTo(0, 52); ctx.lineTo(73, 60); ctx.lineTo(138, 48); ctx.lineTo(256, 67); ctx.stroke();
  const t = new THREE.CanvasTexture(c); t.wrapS = t.wrapT = THREE.RepeatWrapping; t.colorSpace = THREE.SRGBColorSpace;
  return t;
}

export function ribbonBand(track, inner, outer, y, material) {
  const positions = [], indices = [], n = track.waypoints.length;
  for (let i = 0; i < n; i++) {
    const p = track.waypoints[i], normal = track._normals[i];
    for (const offset of [inner, outer]) positions.push(p.x + normal.nx * offset, y, p.z + normal.nz * offset);
    const a = i * 2, b = ((i + 1) % n) * 2;
    indices.push(a, a + 1, b, a + 1, b + 1, b);
  }
  const g = new THREE.BufferGeometry(); g.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
  g.setIndex(indices); g.computeVertexNormals(); material.side = THREE.DoubleSide;
  const m = new THREE.Mesh(g, material); m.receiveShadow = true; track.group.add(m);
}

export function dressRoad(track) {
  const half = track.width / 2;
  const pavement = new THREE.MeshStandardMaterial({ color: 0x515b66, roughness: 0.9 });
  const stripe = new THREE.MeshStandardMaterial({ color: 0xd3dad5, roughness: 0.8 });
  for (const side of [-1, 1]) {
    ribbonBand(track, side * (half + 0.12), side * (half + 4.2), 0.12, pavement);
    ribbonBand(track, side * (half - 1.3), side * (half - 1.12), 0.052, stripe);
  }
  const postMat = new THREE.MeshStandardMaterial({ color: 0x677887, roughness: 0.4, metalness: 0.7 });
  const posts = new THREE.InstancedMesh(new THREE.BoxGeometry(0.14, 1.2, 0.18), postMat, track.waypoints.length);
  const dummy = new THREE.Object3D(); let count = 0;
  for (let i = 0; i < track.waypoints.length; i += 3) for (const side of [-1, 1]) {
    const p = track.waypoints[i], normal = track._normals[i];
    dummy.position.set(p.x + normal.nx * (half + 0.10) * side, 0.65, p.z + normal.nz * (half + 0.10) * side);
    dummy.rotation.y = track.poseAt(i).heading; dummy.updateMatrix(); posts.setMatrixAt(count++, dummy.matrix);
  }
  posts.count = count; posts.instanceMatrix.needsUpdate = true; track.group.add(posts);
  // Corner markers face approaching drivers and show the actual direction of the turn.
  for (let i = 8; i < track.waypoints.length; i += 12) {
    const a = track.waypoints[i - 4], b = track.waypoints[i], c = track.waypoints[(i + 4) % track.waypoints.length];
    const turn = (b.x - a.x) * (c.z - b.z) - (b.z - a.z) * (c.x - b.x);
    if (Math.abs(turn) < 25) continue;
    const pose = track.poseAt(i), edge = turn > 0 ? track.edgePoint(i, half + 1) : track.innerPoint(i, half + 1);
    const tex = signTexture(turn > 0 ? '‹ ‹ ‹' : '› › ›', 'CORNER', track.accent);
    const mat = registerGlow(track._glows, new THREE.MeshStandardMaterial({ map: tex, emissiveMap: tex, emissive: 0xffffff, emissiveIntensity: 0.65, side: THREE.DoubleSide }), { floor: 0.22 });
    const sign = new THREE.Mesh(new THREE.PlaneGeometry(5, 1.7), mat);
    sign.position.set(edge.x, 2.4, edge.z); sign.rotation.y = pose.heading + Math.PI; track.group.add(sign);
  }
  // Starting grid boxes run along the track rather than assuming a straight origin.
  for (let i = 0; i < 4; i++) for (const lane of [-5, 5]) {
    const p = track.poseAt(-2 - i * 2, lane), g = new THREE.Group();
    g.position.set(p.x, 0.057, p.z); g.rotation.y = p.heading;
    for (const x of [-1.4, 1.4]) {
      const m = new THREE.Mesh(new THREE.BoxGeometry(0.10, 0.015, 4.4), stripe); m.position.x = x; g.add(m);
    }
    const m = new THREE.Mesh(new THREE.BoxGeometry(2.9, 0.015, 0.10), stripe); m.position.z = 2.2; g.add(m); track.group.add(g);
  }
}

export function signTexture(title, subtitle, accent = 0x22e3ff) {
  const c = document.createElement('canvas'); c.width = 1024; c.height = 192;
  const ctx = c.getContext('2d'); ctx.fillStyle = '#101923'; ctx.fillRect(0, 0, 1024, 192);
  ctx.fillStyle = '#' + accent.toString(16).padStart(6, '0'); ctx.fillRect(0, 0, 12, 192); ctx.fillRect(1012, 0, 12, 192);
  ctx.font = 'bold 66px Arial'; ctx.textAlign = 'center'; ctx.fillStyle = '#eaf1ed'; ctx.fillText(title.toUpperCase(), 512, 94, 930);
  ctx.font = '23px Arial'; ctx.fillStyle = '#92afb9'; ctx.fillText(subtitle, 512, 144, 930);
  const tex = new THREE.CanvasTexture(c); tex.colorSpace = THREE.SRGBColorSpace; return tex;
}
