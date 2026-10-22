import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * Public animation bundle.
 *
 * - Scroll triggers animate sections in when they enter the viewport.
 * - Parallax + hover micro-interactions run on the main thread and never block.
 * - Respect prefers-reduced-motion by zeroing all motion via a data attribute.
 * - No requestAnimationFrame loops, no layout thrashing, no heavy wrappers.
 */

// ---------------------------------------------------------------------------
// Reduced motion preamble
// ---------------------------------------------------------------------------

const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Split a CSS "color" into R,G,B numbers (handles 3/6/8-digit hex and named).
 */
function parseColor(color) {
    const m = String(color).trim().match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
    if (m) return [Number(m[1]), Number(m[2]), Number(m[3])];
    const h = String(color).trim().replace('#', '');
    const n = h.length === 3 ? [h[0] + h[0], h[1] + h[1], h[2] + h[2]] : h;
    return [parseInt(n.slice(0, 2), 16), parseInt(n.slice(2, 4), 16), parseInt(n.slice(4, 6), 16)];
}

function lerp(a, b, t) { return a + (b - a) * t; }

function styleEl(el, styles) {
    Object.assign(el.style, styles);
}

// ---------------------------------------------------------------------------
// Startup (runs after the DOM is ready)
// ---------------------------------------------------------------------------

const OWN = 'data-node';

function ready(cb) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', cb);
    } else {
        cb();
    }
}

function isReducedMotion() {
    return REDUCED || document.documentElement.dataset.reducedMotion === 'true';
}

function addReducedMotionAttrs() {
    if (isReducedMotion()) {
        // Zero out motion classes; GSAP keeps running frames but with 0 duration,
        // which still forces layout/layout-free transforms per tick (cheap).
        document.documentElement.dataset.reducedMotion = 'true';
    }
}

// ---------------------------------------------------------------------------
// GSAP timeline builder with a reduced-motion guard
// ---------------------------------------------------------------------------

class Reveal {
    constructor(el, vars) {
        this.el = el;
        this.vars = vars;
        this.created = false;
    }

    create(tl) {
        if (this.created) return;
        this.created = true;

        const sty = this.el.style;

        // In reduced motion everything just appears, no offset.
        const offsetX = isReducedMotion() ? 0 : this.vars.offsetX || 0;
        const offsetY = isReducedMotion() ? 0 : this.vars.offsetY || 0;
        const dur = isReducedMotion() ? 0 : (this.vars.duration || 0.6);
        const ease = this.vars.ease || 'power3.out';

        // Fade in + slide up from above/left.
        gsap.set(this.el, { clearProps: 'all' });
        gsap.set(this.el, {
            opacity: 0,
            y: isReducedMotion() ? 0 : offsetY,
            x: isReducedMotion() ? 0 : offsetX
        });

        const ctrl = ScrollTrigger.create({
            trigger: this.el,
            start: 'top 85%',
            end: 'bottom 10%',
            toggleActions: 'play none none reverse',
            onEnter: () => {
                gsap.to(this.el, { opacity: 1, y: 0, x: 0, duration: dur, ease, overwrite: true });
            },
            onLeaveBack: () => {
                if (!isReducedMotion()) {
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

// ---------------------------------------------------------------------------
// Public-scene helpers (keep DOM present; use opacity/transform only)
// ---------------------------------------------------------------------------

function revealSection(target, cfg) {
    // Accept a selector string or a DOM element (observers pass elements).
    const el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!el) return null;
    const vars = {
        offsetX: 40,
        offsetY: 32,
        duration: 0.6,
        ease: 'power3.out',
        ...cfg,
    };
    return new Reveal(el, vars);
}

function pauseReveal(reveal) {
    reveal && reveal.kill();
}

// Hover micro-interaction: lift the nearest `.neon-card-hover` sibling/group
// and lift the "Book →" button slightly, plus a subtle glow.
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

// Color-shift on hover for in-browser click/tap demos (no layout).
function bindHoverGlow(root = document) {
    root.querySelectorAll('.neon-card-hover').forEach((card) => {
        const glow = document.createElement('span');
        glow.className = 'neon-hover-glow';
        glow.style.cssText =
            'position:fixed;inset:0;z-index:-1;pointer-events:none;border-radius:2rem;opacity:0;transition:opacity .3s';
        card.appendChild(glow);
        const on = () => { gsap.to(glow, { opacity: 1, duration: 0.25, overwrite: true }); };
        const off = () => { gsap.to(glow, { opacity: 0, duration: 0.3, ease: 'power2.in', overwrite: true }); };
        card.addEventListener('mouseenter', on);
        card.addEventListener('mouseleave', off);
        card.addEventListener('mousemove', (e) => {
            gsap.to(glow, { x: e.clientX - card.clientWidth / 2, y: e.clientY - card.clientHeight / 2, duration: 0, overwrite: true });
        });
    });
}

// Subtle race "neon" flash on the "Book →" buttons of featured cards.
function bindBookPulse(root = document) {
    root.querySelectorAll('.neon-card .btn-neon').forEach((btn) => {
        const orig = btn.textContent;
        let fired = false;
        btn.addEventListener('mouseenter', () => {
            if (fired) return;
            fired = true;
            gsap.to(btn, {
                scale: 1.05, y: -1.5, duration: 0.2, ease: 'power2.out', overwrite: true
            });
            gsap.to(btn, {
                scale: 1, y: 0, duration: 0.25, ease: 'power2.in', delay: 0.18, overwrite: true
            }).then(() => { fired = false; });
        });
        btn.addEventListener('focus', () => {
            gsap.to(btn, { boxShadow: '0 0 0 3px rgba(255,45,85,0.45)', duration: 0.2, overwrite: true });
        });
        btn.addEventListener('blur', () => {
            gsap.to(btn, { boxShadow: 'none', duration: 0.2, overwrite: true });
        });
    });
}

// Tall parallax on the home hero using a data attribute to stay performant.
function bindHeroParallax(root = document) {
    root.querySelectorAll('[data-parallax]').forEach((el) => {
        const speed = parseFloat(el.dataset.parallax) || 0.18;
        window.addEventListener('scroll', () => {
            const dy = window.pageYOffset;
            styleEl(el, { transform: `translateY(${dy * speed}px)` });
        }, { passive: true });
    });
}

// Initial page-load fade-ins (fade-in when element is in view, no scrub).
function fadeInObservations(root = document) {
    root.querySelectorAll('[data-fade-in]').forEach((el) => {
        const dur = parseFloat(el.dataset.fadeInDur) || 0.5;
        const y = parseFloat(el.dataset.fadeInY) || 12;
        gsap.fromTo(el, { opacity: 0, y: y }, { opacity: 1, y: 0, duration: dur, ease: 'power2.out', delay: 0.08 });
    });
}

// ---------------------------------------------------------------------------
// Reduced motion: drop all animation distances to zero
// ---------------------------------------------------------------------------

function applyReducedMotion() {
    if (isReducedMotion()) return;
    // Zero out any in-flight GSAP values without creating new tweens.
    gsap.globalTimeline.kill();
    // Keep the structural DOM but reset transforms. Drag handles are left alone.
    document.querySelectorAll('[data-parallax]').forEach((el) => { el.style.transform = 'translateY(0)'; });
}

// ---------------------------------------------------------------------------
// Enter viewport: lightweight IntersectionObserver used where a scroll trigger
// is overkill (only for observe-on-load lazy behaviours).
// ---------------------------------------------------------------------------

function observeEnter(selector, cb, root = null) {
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                cb(e.target);
                io.unobserve(e.target);
            }
        });
    }, { root, threshold: 0.35 });
    document.querySelectorAll(selector).forEach((el) => io.observe(el));
    return io;
}

// ---------------------------------------------------------------------------
// Mobile card sliders
// ---------------------------------------------------------------------------

/**
 * Turns every [data-slider] block into a swipeable carousel on phones:
 * builds the pagination dots, keeps the active dot in sync with the scroll
 * position (manual swipes included) and rebuilds when cards are filtered in
 * or out of view. From `sm` upwards the underlying layout is a normal grid and
 * the dots stay hidden, so desktop is untouched.
 */
function initCardSliders(root = document) {
    root.querySelectorAll('[data-slider]').forEach((slider) => {
        if (slider.dataset.sliderReady === '1') return;

        const track = slider.querySelector('.mobile-slider__track');
        const dots = slider.querySelector('[data-slider-dots]');
        if (!track || !dots) return;

        slider.dataset.sliderReady = '1';

        let cards = [];
        let step = 0;
        let signature = '';
        let scrollFrame = null;
        let buildFrame = null;

        const visibleCards = () => Array.from(track.children).filter((el) => {
            if (!(el instanceof HTMLElement)) return false;
            return el.offsetParent !== null && getComputedStyle(el).display !== 'none';
        });

        function measure() {
            cards = visibleCards();
            const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
            step = cards.length ? cards[0].getBoundingClientRect().width + gap : 0;

            return step > 0;
        }

        function currentIndex() {
            if (!step) return 0;

            const max = Math.max(0, track.scrollWidth - track.clientWidth);
            const left = Math.min(track.scrollLeft, max);

            return Math.max(0, Math.min(cards.length - 1, Math.round(left / step)));
        }

        function paint() {
            const index = currentIndex();

            Array.from(dots.children).forEach((dot, i) => {
                const active = i === index;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
            });
        }

        function build() {
            // Hover animations write inline transforms on the cards, which also
            // trip the mutation observer. Skip the rebuild when nothing that
            // affects the pagination actually changed.
            if (!measure()) {
                dots.classList.add('hidden');
                return;
            }

            const nextSignature = cards.length + ':' + track.clientWidth + ':' + track.scrollWidth;
            if (nextSignature === signature) {
                paint();
                return;
            }
            signature = nextSignature;

            const scrollable = track.scrollWidth > track.clientWidth + 4;

            dots.innerHTML = '';

            if (!scrollable || cards.length < 2) {
                dots.classList.add('hidden');
                return;
            }

            dots.classList.remove('hidden');

            cards.forEach((card, i) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'mobile-slider__dot';
                dot.setAttribute('aria-label', 'Go to card ' + (i + 1));
                dot.addEventListener('click', () => {
                    track.scrollTo({
                        left: i * step,
                        behavior: isReducedMotion() ? 'auto' : 'smooth',
                    });
                });
                dots.appendChild(dot);
            });

            paint();
        }

        function scheduleBuild() {
            if (buildFrame) return;
            buildFrame = requestAnimationFrame(() => {
                buildFrame = null;
                build();
            });
        }

        track.addEventListener('scroll', () => {
            if (scrollFrame) return;
            scrollFrame = requestAnimationFrame(() => {
                scrollFrame = null;
                paint();
            });
        }, { passive: true });

        window.addEventListener('resize', scheduleBuild, { passive: true });

        // Filters (Alpine x-show) add/remove cards and the grid reflows on
        // orientation changes — rebuild the dots when that happens.
        if ('MutationObserver' in window) {
            new MutationObserver(scheduleBuild).observe(track, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class'],
            });
        }

        if ('ResizeObserver' in window) {
            new ResizeObserver(scheduleBuild).observe(track);
        }

        build();
    });
}

// ---------------------------------------------------------------------------
// Boot
// ---------------------------------------------------------------------------

ready(() => {
    // 1. Reduced-motion preference applied before any GSAP runs.
    addReducedMotionAttrs();

    // 2. Hero parallax (cheap; no RAF loop).
    bindHeroParallax();

    // 3. Hover micro-interactions (card lift, glow, button pulse).
    bindHoverLift();
    bindHoverGlow();
    bindBookPulse();

    // 4. Scroll reveals for sections (GSAP ScrollTrigger).
    const reveals = [
        revealSection('.neon-card, [data-reveal]', { offsetX: 40, offsetY: 32, duration: 0.6 }), // generic fallback
        revealSection('.featured, section[id]', { offsetX: 48, offsetY: 40, duration: 0.62, ease: 'power3.out' }),
        revealSection('footer', { offsetX: 0, offsetY: 18, duration: 0.5, ease: 'power2.out' }),
    ].filter(Boolean);

    // 5. Initial fade-ins for elements marked for a lighter entrance.
    fadeInObservations();

    // 5b. Swipeable mobile card sliders (pagination dots stay in sync).
    initCardSliders();

    // 6. Ensure the body never gets `overflow:hidden`/`clip` — animations never
    //    block scrolling or clicking.
    const rootEl = document.documentElement;
    if (rootEl.style.overflow !== 'hidden') {
        rootEl.style.overflow = 'visible';
    }

    // Expose a tiny API for page-level scripts that need tight control.
    window.BOOKDOM = { pauseReveal, reloadReveals: () => {} };

    // Lazily reveal the planner (activity page) when it mounts; the public pages
    // that don't extend this bundle simply don't use these reveals.
    observeEnter('[data-reveal], section[id], [data-parallax]', (el) => {
        if (!el.dataset.revealInitialized) {
            el.dataset.revealInitialized = '1';
            const r = revealSection(el, { offsetX: 48, offsetY: 40, duration: 0.6 });
            window.BOOKDOM.reveals = window.BOOKDOM.reveals || [];
            window.BOOKDOM.reveals.push(r);
        }
    });
});

// Expose GSAP globally for inline scripts (activity page).
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;
