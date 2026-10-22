// Replicates CLI screenshot conditions but inspects which CSS was applied.
import { spawn } from 'node:child_process';
import { writeFileSync } from 'node:fs';

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const URL_PAGE = 'http://127.0.0.1:8000/demo';
const PORT = 9227;
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const chrome = spawn(CHROME, [
  '--headless=new', '--disable-gpu', '--no-sandbox',
  '--force-device-scale-factor=1',
  `--remote-debugging-port=${PORT}`,
  '--user-data-dir=C:/tmp/chrome-sim',
  '--window-size=1440,900', URL_PAGE,
], { stdio: 'ignore' });
process.on('exit', () => { try { chrome.kill(); } catch {} });

async function getTarget() {
  for (let i = 0; i < 40; i++) {
    try {
      const list = await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json();
      const page = list.find((t) => t.type === 'page' && t.url.includes('127.0.0.1:8000'));
      if (page) return page;
    } catch {}
    await sleep(500);
  }
  throw new Error('no target');
}

const target = await getTarget();
const ws = new WebSocket(target.webSocketDebuggerUrl);
let nextId = 1;
const pending = new Map();
ws.addEventListener('message', (ev) => {
  const msg = JSON.parse(ev.data);
  if (msg.id && pending.has(msg.id)) { pending.get(msg.id)(msg); pending.delete(msg.id); }
});
const send = (method, params = {}) => new Promise((resolve) => {
  const id = nextId++;
  pending.set(id, resolve);
  ws.send(JSON.stringify({ id, method, params }));
});
await new Promise((r) => ws.addEventListener('open', r));
await send('Runtime.enable');
await send('Page.enable');
await send('Page.navigate', { url: URL_PAGE });
await sleep(6000);

const { result } = await send('Runtime.evaluate', {
  returnByValue: true,
  expression: `(() => {
    const links = [...document.querySelectorAll('link[rel=stylesheet]')].map(l => l.href);
    const heroImg = document.querySelector('section img.opacity-\\\\[0\\\\.14\\\\], section img[class*="opacity"]');
    const hero = document.querySelector('section[id], section');
    const grid = document.querySelector('.lg\\\\:grid-cols-\\\\[1\\\\.08fr_0\\\\.92fr\\\\]') || hero && hero.querySelector('[class*="grid-cols"]');
    return {
      cssLinks: links,
      dpr: window.devicePixelRatio,
      innerWidth: window.innerWidth,
      heroImgOpacity: heroImg ? getComputedStyle(heroImg).opacity : 'n/a',
      heroImgSrc: heroImg ? heroImg.src.slice(-40) : 'n/a',
      gridCols: grid ? getComputedStyle(grid).gridTemplateColumns : 'n/a',
      bodyBg: getComputedStyle(document.body).backgroundColor,
    };
  })()`,
});
const probe = async (tag) => {
  const { result } = await send('Runtime.evaluate', {
    returnByValue: true,
    expression: `(() => {
      const imgs = [...document.querySelectorAll('img')].filter(i => i.src.includes('esports-arena'));
      const hero = document.querySelector('section');
      const grid = hero ? hero.querySelector('[class*="grid-cols"]') : null;
      return {
        esportsImgs: imgs.map(i => ({ op: getComputedStyle(i).opacity, w: Math.round(i.getBoundingClientRect().width),
          y: Math.round(i.getBoundingClientRect().top), disp: getComputedStyle(i).display })),
        gridCols: grid ? getComputedStyle(grid).gridTemplateColumns : 'n/a',
        innerWidth: window.innerWidth,
        readyState: document.readyState,
      };
    })()`,
  });
  console.log(tag, JSON.stringify(result?.value ?? result));
  return result?.value;
};

await probe('BEFORE-SHOT:');
// Canary: if the screenshot lacks this marker, the capture is stale/foreign.
await send('Runtime.evaluate', { returnByValue: true, expression: `(() => {
  const d = document.createElement('div');
  d.id = 'canary-marker';
  d.textContent = 'CANARY-LIVE';
  d.style.cssText = 'position:fixed;top:0;left:0;z-index:99999;background:#0f0;color:#000;font:24px monospace;padding:8px;';
  document.body.appendChild(d);
  const bgs = [];
  for (const el of document.querySelectorAll('*')) {
    const cs = getComputedStyle(el);
    if (cs.backgroundImage && cs.backgroundImage !== 'none') {
      const r = el.getBoundingClientRect();
      if (r.width > 200 && r.height > 200) bgs.push({ tag: el.tagName, cls: String(el.className).slice(0,60),
        bg: cs.backgroundImage.slice(0,110), w: Math.round(r.width), h: Math.round(r.height), y: Math.round(r.top), op: cs.opacity });
    }
  }
  return bgs;
})()` }).then(r => console.log('BGS', JSON.stringify(r.result?.result?.value ?? r).slice(0, 1500)));
const shot = await send('Page.captureScreenshot', { format: 'png' });
writeFileSync('verify_sim.png', Buffer.from(shot.result.data, 'base64'));
await probe('AFTER-SHOT: ');

ws.close();
chrome.kill();
process.exit(0);
