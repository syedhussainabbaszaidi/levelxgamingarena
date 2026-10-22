import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Alpine from 'alpinejs';

gsap.registerPlugin(ScrollTrigger);

/**
 * Alpine powers the topbar dropdowns (profile/logout menu) in the admin and
 * public layouts via x-data="{ open: false }". It must be imported and started
 * here — the package is installed but only takes effect once booted.
 */
window.Alpine = Alpine;
Alpine.start();

/**
 * Reusable GSAP toolkit shared by the admin shell and any page that needs
 * scroll-linked motion. It keeps the DOM in the document at all times, avoids
 * layout thrashing and honours prefers-reduced-motion.
 */

const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const IS_REDUCED = () => REDUCED || document.documentElement.dataset.reducedMotion === 'true';

function addReducedMotionAttrs() {
    if (!IS_REDUCED()) return;
    document.documentElement.dataset.reducedMotion = 'true';
}

class Reveal {
    constructor(el, vars) {
        this.el = el;
        this.vars = vars;
        this.created = false;
    }

    create(tl) {
        if (this.created) return;
        this.created = true;

        const isRed = IS_REDUCED();
        const offsetX = isRed ? 0 : (this.vars.offsetX ?? 0);
        const offsetY = isRed ? 0 : (this.vars.offsetY ?? 0);
        const dur = isRed ? 0 : (this.vars.duration ?? 0.6);
        const ease = this.vars.ease ?? 'power3.out';

        gsap.set(this.el, { clearProps: 'all' });
        gsap.set(this.el, { opacity: 0, y: offsetY, x: offsetX });

        const ctrl = ScrollTrigger.create({
            trigger: this.el,
            start: 'top 85%',
            end: 'bottom 10%',
            toggleActions: 'play none none reverse',
            onEnter: () => {
                gsap.to(this.el, { opacity: 1, y: 0, x: 0, duration: dur, ease, overwrite: true });
            },
            onLeaveBack: () => {
                if (!isRed) {
                    gsap.to(this.el, { opacity: 0, y: offsetY, x: offsetX, duration: 0.35, ease: 'power2.in' });
                }
            }
        });
        return { ctrl, duration: dur };
    }

    kill() {
        if (this.created) {
            ScrollTrigger.getAll().forEach((t) => { if (t && t.vars.trigger === this.el) t.kill(); });
        }
    }
}

function reveal(target, cfg) {
    // Accept a selector string or a DOM element (boot() passes elements).
    const el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!el) return null;
    const vars = { offsetX: 40, offsetY: 32, duration: 0.6, ease: 'power3.out', ...cfg };
    return new Reveal(el, vars);
}

function pauseReveal(reveal) {
    reveal && reveal.kill();
}

function bindHoverLift(root = document) {
    root.querySelectorAll('.neon-card-hover').forEach((card) => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, { scale: 1.015, y: -3, duration: 0.28, ease: 'power2.out', overwrite: true });
            gsap.to(card.querySelector('.btn-neon, .btn-ghost'), {
                scale: 1.04, y: -2, duration: 0.28, ease: 'power2.out', overwrite: true
            });
        });
        card.addEventListener('mouseleave', () => {
            gsap.to(card, { scale: 1, y: 0, duration: 0.3, ease: 'power2.in', overwrite: true });
            gsap.to(card.querySelector('.btn-neon, .btn-ghost'), {
                scale: 1, y: 0, duration: 0.3, ease: 'power2.in', overwrite: true
            });
        });
    });
}

function bindHoverGlow(root = document) {
    root.querySelectorAll('.neon-card-hover').forEach((card) => {
        const glow = document.createElement('span');
        glow.className = 'neon-hover-glow';
        glow.style.cssText =
            'position:fixed;inset:0;z-index:-1;pointer-events:none;border-radius:2rem;opacity:0;transition:opacity .3s';
        card.appendChild(glow);
        const on = () => gsap.to(glow, { opacity: 1, duration: 0.25, overwrite: true });
        const off = () => gsap.to(glow, { opacity: 0, duration: 0.3, ease: 'power2.in', overwrite: true });
        card.addEventListener('mouseenter', on);
        card.addEventListener('mouseleave', off);
        card.addEventListener('mousemove', (e) => {
            gsap.to(glow, { x: e.clientX - card.clientWidth / 2, y: e.clientY - card.clientHeight / 2, duration: 0, overwrite: true });
        });
    });
}

// Parallax elements drift slowly on scroll (passive listener, never blocks).
function bindParallax(root = document) {
    root.querySelectorAll('[data-parallax]').forEach((el) => {
        const speed = parseFloat(el.dataset.parallax) || 0.18;
        window.addEventListener('scroll', () => {
            const dy = window.pageYOffset;
            el.style.transform = `translateY(${dy * speed}px)`;
        }, { passive: true });
    });
}

/**
 * Keeps every [data-live-clock] element (top navigation) in sync with the
 * current date, time and timezone without a page reload.
 */
function startClock(root = document) {
    const nodes = root.querySelectorAll('[data-live-clock]');
    if (!nodes.length) return;

    const tick = () => {
        const now = new Date();
        nodes.forEach((node) => {
            const tz = node.getAttribute('data-timezone') || undefined;
            const dateEl = node.querySelector('[data-clock-date]');
            const timeEl = node.querySelector('[data-clock-time]');
            const zoneEl = node.querySelector('[data-clock-zone]');
            try {
                if (dateEl) {
                    dateEl.textContent = now.toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short', timeZone: tz });
                }
                if (timeEl) {
                    timeEl.textContent = now.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit', hour12: true, timeZone: tz });
                }
                if (zoneEl) {
                    const parts = new Intl.DateTimeFormat(undefined, { timeZone: tz, timeZoneName: 'short' }).formatToParts(now);
                    const zone = parts.filter((p) => p.type === 'timeZoneName')[0];
                    zoneEl.textContent = zone ? zone.value : (tz || '');
                }
            } catch (err) {
                /* invalid timezone — keep the server-rendered value */
            }
        });
    };

    tick();
    setInterval(tick, 15000);
}

// Note: since the admin shell is a static server-rendered page, we register a
// one-time "observe sections" pass rather than re-adding triggers on every
// navigation, keeping ScrollTrigger lightweight.
function boot(windowObj = window) {
    const doc = windowObj.document;

    addReducedMotionAttrs();

    // Reveals for admin sections.
    const sections = doc.querySelectorAll('[data-reveal], section[id]');
    const reveals = [];
    sections.forEach((el) => {
        const r = reveal(el, { offsetX: 48, offsetY: 40, duration: 0.62, ease: 'power3.out' });
        if (r) reveals.push(r);
    });

    // Hover micro-interactions.
    bindHoverLift(doc);
    bindHoverGlow(doc);

    // Parallax (cheap, passive).
    bindParallax(doc);

    // Live top-navigation clock.
    startClock(doc);

    // Expose a small API.
    windowObj.BOOKDOM = { pauseReveal, reveals };
    windowObj.BOOKDOM.reloadReveals = () => { reveals.forEach((r) => r.kill()); };
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => boot(window));
} else {
    boot(window);
}

window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;
