/**
 * Intro Animation Module - Rebuilt
 * 
 * Handles the cinematic intro animation sequence using GSAP.
 * Configuration loaded from PHP-generated JSON in page.
 */

// Utility functions
const $ = s => document.querySelector(s);
const $$ = s => [...document.querySelectorAll(s)];
const vw = () => window.innerWidth;
const vh = () => window.innerHeight;
const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

// Check if GSAP is available
if (typeof gsap === 'undefined') {
    console.warn('GSAP not loaded, intro animation disabled');
    document.body.classList.remove('intro-on');
    document.body.classList.add('ready');
    $('#site')?.setAttribute('aria-hidden', 'false');
    gsap = { 
        timeline: () => ({ 
            to: () => {}, 
            fromTo: () => {}, 
            set: () => {}, 
            addLabel: () => {}, 
            call: () => {}, 
            progress: () => {}, 
            restart: () => {} 
        }) 
    };
}

/**
 * Parse intro configuration from DOM
 */
function parseIntroConfig() {
    const chainCards = $$('.chain-card');
    const collageMain = $('.collage-card.main-card');
    const collageSides = $$('.collage-card:not(.main-card)');
    
    // Parse chain cards
    const chain = chainCards.map(card => ({
        el: card,
        gif: card.dataset.gif,
        rotation: parseFloat(card.dataset.rotation) || 0,
        enterFrom: card.dataset.enterFrom || 'left',
        art: card.querySelector('.art img')
    }));
    
    // Parse main collage card
    const mainCard = collageMain ? {
        el: collageMain,
        gif: collageMain.dataset.gif,
        landscape: collageMain.dataset.landscape === 'true',
        art: collageMain.querySelector('.art img')
    } : null;
    
    // Parse side collage cards
    const sides = collageSides.map(card => ({
        el: card,
        gif: card.dataset.gif,
        position: card.dataset.position || 'bottom',
        landscape: card.classList.contains('land'),
        art: card.querySelector('.art img')
    }));
    
    return { chain, mainCard, sides };
}

/**
 * Get responsive position for collage cards
 */
function getCollagePositions(P) {
    // Desktop positions
    if (!P) {
        return [
            { key: 'rainbow', fx: .5, fy: .155, rot: 3.5, s: .9, dir: 'top', rx: 6 },
            { key: 'bunny', fx: .215, fy: .40, rot: -7, s: .9, dir: 'left', ry: 8 },
            { key: 'cat', fx: .785, fy: .41, rot: 6, s: .92, dir: 'right', ry: -8 },
            { key: 'star', fx: .30, fy: .735, rot: -4.5, s: .86, dir: 'bl' },
            { key: 'controller', fx: .70, fy: .755, rot: 5, s: .88, dir: 'br' },
            { key: 'burger', fx: .51, fy: .875, rot: -3, s: .76, dir: 'bottom' }
        ];
    }
    // Mobile/portrait positions
    return [
        { key: 'star', fx: .5, fy: .11, rot: 3.5, s: .78, dir: 'top' },
        { key: 'bunny', fx: .17, fy: .33, rot: -8, s: .85, dir: 'left' },
        { key: 'cat', fx: .83, fy: .35, rot: 7, s: .85, dir: 'right' },
        { key: 'icecream', fx: .26, fy: .67, rot: -5, s: .85, dir: 'bl' },
        { key: 'controller', fx: .74, fy: .70, rot: 5, s: .85, dir: 'br' },
        { key: 'rainbow', fx: .5, fy: .87, rot: -3, s: .74, dir: 'bottom' }
    ];
}

/**
 * Get offset positions for collage card directions
 */
function getCollageOffsets(W, H) {
    const X = f => (f - .5) * W;
    const Y = f => (f - .5) * H;
    
    return {
        top: { sx: X(.5), sy: -H * .85 }, 
        left: { sx: -W * .8, sy: Y(.45) },
        right: { sx: W * .8, sy: Y(.45) }, 
        bl: { sx: -W * .75, sy: H * .8 },
        br: { sx: W * .75, sy: H * .8 }, 
        bottom: { sx: X(.5), sy: H * .95 }
    };
}

class IntroAnimation {
    constructor() {
        this.hasIntro = !!document.getElementById('intro');
        this.introTL = null;
        this.finished = !this.hasIntro;
        this.chainPara = [];
        this.collageAll = [];
        this.floatTweens = [];
        this.chain = [];
        this.sides = [];
        this.mainCard = null;
        this.chainPos = [];
        this.rots = [];
        this.W = 0;
        this.H = 0;
        this.P = false;
        this.X = null;
        this.Y = null;
        
        // Bind methods
        this.finishIntro = this.finishIntro.bind(this);
        this.startCollageFloats = this.startCollageFloats.bind(this);
        this.killCollageFloats = this.killCollageFloats.bind(this);
        this.buildIntro = this.buildIntro.bind(this);
    }

    init() {
        if (!this.hasIntro) return;
        
        const config = parseIntroConfig();
        this.chain = config.chain;
        this.sides = config.sides;
        this.mainCard = config.mainCard;
        
        if (this.chain.length === 0 || this.sides.length === 0 || !this.mainCard) {
            console.warn('Intro elements not found, skipping animation');
            this.finishIntro();
            return;
        }
        
        this.buildIntro();
        
        // Set up parallax for chain cards
        this.chainPara = this.chain.map((c, i) => ({ 
            el: c.art, 
            depth: 8 + (i % 3) * 7 
        }));
        
        // Set up skip button
        const skipBtn = $('#skipBtn');
        if (skipBtn) {
            skipBtn.addEventListener('click', () => { 
                this.introTL.progress(1, false); 
                this.finishIntro(); 
            });
        }
        
        // Set up replay button
        const replayBtn = $('#replayBtn');
        if (replayBtn) {
            replayBtn.addEventListener('click', () => this.replayIntro());
        }
        
        // Start animation
        makePara(this.chainPara);
        gsap.to('#skipBtn', { autoAlpha: 1, delay: 1.2, duration: .4 });
        
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) { 
            this.introTL.progress(1, false); 
            this.finishIntro(); 
        }
    }

    buildIntro() {
        const chain = this.chain;
        const sideEls = this.sides.map(s => s.el);
        const mainCard = this.mainCard.el;
        
        // Initial state setup
        chain.forEach((c, i) => gsap.set(c.el, { xPercent: -50, yPercent: -50, autoAlpha: 0, zIndex: 1 + i }));
        sideEls.forEach((c, i) => gsap.set(c, { xPercent: -50, yPercent: -50, autoAlpha: 0, zIndex: 20 + i }));
        gsap.set(mainCard, { xPercent: -50, yPercent: -50, autoAlpha: 0, zIndex: 50 });
        
        this.collageAll = [mainCard, ...sideEls];
        
        gsap.set('#whitePanel', { xPercent: -50, yPercent: -50, scale: 0, rotation: -10 });
        gsap.set('#brandMark', { xPercent: -50, yPercent: -50 });
        gsap.set(['.t-line', '.tagline', '.ticket', '.site-head'], { autoAlpha: 0 });
        gsap.set('.floater', { autoAlpha: 0 });
        gsap.set('#site', { scale: 1.1 });

        this.W = vw();
        this.H = vh();
        this.P = this.W / this.H < .85;
        this.X = f => (f - .5) * this.W;
        this.Y = f => (f - .5) * this.H;
        
        this.introTL = gsap.timeline({ delay: .25, onComplete: this.finishIntro });

        /* PHASE 1 — the card chain */
        const stepX = this.P ? .085 : .115;
        const stepY = this.P ? .125 : .107;
        this.rots = chain.map(c => c.rotation);
        this.chainPos = chain.map((c, i) => ({
            fx: .84 - i * stepX, 
            fy: (this.P ? .13 : .16) + i * stepY,
            tx: this.X(.84 - i * stepX), 
            ty: this.Y((this.P ? .13 : .16) + i * stepY)
        }));
        
        const chainArray = Array.from(chain);
        chainArray.forEach((card, i) => {
            const p = this.chainPos[i];
            const fromLeft = card.enterFrom === 'left';
            this.introTL.set(card.el, {
                x: fromLeft ? -this.W * 1.15 : p.tx, 
                y: fromLeft ? p.ty : this.H * 1.2,
                rotation: this.rots[i] * 2.3, 
                scale: .92, 
                autoAlpha: 1
            }, 0);
            this.introTL.to(card.el, { 
                x: p.tx, 
                y: p.ty, 
                rotation: this.rots[i], 
                scale: 1, 
                duration: .95, 
                ease: 'power3.out' 
            }, .3 + i * .4);
            this.introTL.fromTo(card.art,
                { scaleX: 1.16, scaleY: .9 },
                { scaleX: 1, scaleY: 1, duration: .55, ease: 'elastic.out(1,.45)', immediateRender: false },
                .3 + i * .4 + .5);
        });

        /* PHASE 2 — scatter */
        this.introTL.addLabel('scatter', 3.9);
        chainArray.forEach((card, i) => {
            const p = this.chainPos[i];
            this.introTL.to(card.el, {
                x: p.tx + (p.fx - .5) * this.W * 1.6, 
                y: p.ty + (p.fy - .5) * this.H * 1.6,
                rotation: this.rots[i] + (i % 2 ? 30 : -36), 
                scale: .8, 
                duration: 0.9, 
                ease: 'power2.in'
            }, 'scatter+=' + i * 0.03);
            this.introTL.to(card.el, { 
                autoAlpha: 0, 
                duration: 0.32, 
                ease: 'none' 
            }, 'scatter+=' + (0.5 + i * .02));
        });
        this.introTL.fromTo('#introStage', { scale: 1 }, { scale: 1.06, duration: 1, ease: 'power2.inOut', immediateRender: false }, 'scatter+=.15');
        this.introTL.fromTo('#whitePanel', { scale: 0, rotation: -10 },
            { scale: 1, rotation: 0, duration: 0.72, ease: 'power4.in', immediateRender: false }, 'scatter+=.5');
        this.introTL.addLabel('white', 'scatter+=' + 1.22);
        this.introTL.set('#introStage', { scale: 1 }, 'white');
        this.introTL.fromTo('#flash', { autoAlpha: 0 }, { autoAlpha: .95, duration: 0.12, ease: 'power2.in', immediateRender: false }, 'white-=0.12');
        this.introTL.to('#flash', { autoAlpha: 0, duration: 0.55, ease: 'power2.out' }, 'white+=0.55');
        this.introTL.fromTo('#brandMark', { autoAlpha: 0, scale: .55, y: 14 },
            { autoAlpha: 1, scale: 1, y: 0, duration: 0.5, ease: 'back.out(2)', immediateRender: false }, 'white+=0.15');
        this.introTL.to('#brandMark', { autoAlpha: 0, scale: .85, duration: 0.28, ease: 'power2.in' }, 'white+=0.62');

        /* PHASE 3 — collage reveal */
        // Build sides array from actual DOM elements with positions
        const defs = getCollagePositions(this.P);
        const offs = getCollageOffsets(this.W, this.H);
        
        this.sides = defs.map(d => {
            // Find matching side element by gif
            const side = this.sides.find(s => s.gif.includes(d.key));
            const el = side ? side.el : null;
            if (!el) return null;
            return { 
                ...d, 
                el, 
                tx: this.X(d.fx), 
                ty: this.Y(d.fy), 
                ...offs[d.dir] 
            };
        }).filter(Boolean);
        
        this.introTL.addLabel('reveal', 'white+=' + 1.02);
        this.sides.forEach((d, i) => {
            this.introTL.set(d.el, { 
                x: d.sx, 
                y: d.sy, 
                rotation: d.rot * 2.2, 
                scale: 1.28, 
                autoAlpha: 1 
            }, 'reveal');
            this.introTL.to(d.el, { 
                x: d.tx * .45, 
                y: d.ty * .45, 
                scale: 1.16, 
                duration: 0.5, 
                ease: 'power3.out' 
            }, 'reveal+=' + (.05 + i * 0.11));
            this.introTL.to(d.el, { 
                x: d.tx, 
                y: d.ty, 
                scale: d.s, 
                rotation: d.rot, 
                rotationX: d.rx || 0, 
                rotationY: d.ry || 0, 
                duration: 0.55, 
                ease: 'back.out(1.5)' 
            }, 'reveal+=' + (.5 + i * 0.11));
        });
        
        this.introTL.fromTo(this.mainCard.el, { scale: 0, rotation: 12, x: 0, y: 0, autoAlpha: 1 },
            { scale: 1, rotation: -1.5, duration: .7, ease: 'back.out(1.6)', immediateRender: false }, 'reveal+=' + 0.78);

        this.introTL.addLabel('formed', 'reveal+=' + 1.5);
        this.introTL.call(this.startCollageFloats, null, 'formed');
        this.introTL.to({}, { duration: 0.85 }, 'formed');

        /* PHASE 4 — explosion */
        this.introTL.addLabel('boom', 'formed+=' + 0.85);
        this.introTL.call(this.killCollageFloats, null, 'boom');
        this.sides.forEach((d, i) => {
            this.introTL.to(d.el, {
                x: d.tx + (d.fx - .5) * this.W * 2.1, 
                y: d.ty + (d.fy - .5) * this.H * 2.1,
                rotation: d.rot + (i % 2 ? 46 : -38), 
                scale: .45,
                duration: 0.95 + (i % 3) * .14, 
                ease: 'power2.in'
            }, 'boom');
            this.introTL.to(d.el, { 
                autoAlpha: 0, 
                duration: .3, 
                ease: 'none' 
            }, 'boom+=' + (0.55 + (i % 3) * 0.07));
        });
        this.introTL.to(this.mainCard.el, { scale: 2.5, autoAlpha: 0, rotation: 10, duration: 0.55, ease: 'power2.in' }, 'boom+=' + 0.55);
        this.introTL.to('#whitePanel', { scale: 1.25, autoAlpha: 0, duration: 0.8, ease: 'power2.inOut' }, 'boom+=' + 0.55);
        this.introTL.to('#introBg', { autoAlpha: 0, duration: 0.8, ease: 'power2.inOut' }, 'boom+=' + 0.55);
        this.introTL.fromTo('#flash', { autoAlpha: 0 }, { autoAlpha: .55, duration: 0.1, immediateRender: false }, 'boom');
        this.introTL.to('#flash', { autoAlpha: 0, duration: 0.5, ease: 'power2.out' }, 'boom+=' + 0.5);
        this.introTL.fromTo('#site', { scale: 1.1 }, { scale: 1, duration: 1.3, ease: 'power3.out', immediateRender: false }, 'boom');

        /* PHASE 5 — site reveal */
        this.introTL.addLabel('site', 'boom+=' + 0.55);
        this.introTL.fromTo('.floater', { autoAlpha: 0, y: 46, scale: .7 },
            { autoAlpha: 1, y: 0, scale: 1, duration: .8, ease: 'back.out(1.8)', stagger: 0.09, immediateRender: false }, 'boom+=' + 0.3);
        this.introTL.fromTo('.t-line', { autoAlpha: 0, y: 56, scale: 1.45, filter: 'blur(10px)' },
            { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 1, ease: 'power4.out', stagger: 0.14, immediateRender: false }, 'site+=' + 0);
        this.introTL.fromTo('.site-head', { autoAlpha: 0, y: -18 }, { autoAlpha: 1, y: 0, duration: .6, ease: 'power2.out', immediateRender: false }, 'site+=' + 0.35);
        this.introTL.fromTo('.tagline', { autoAlpha: 0, y: 22 }, { autoAlpha: 1, y: 0, duration: .7, ease: 'power3.out', immediateRender: false }, 'site+=' + 0.5);
        this.introTL.fromTo('.nav-btn', { autoAlpha: 0, y: 26, scale: .82 },
            { autoAlpha: 1, y: 0, scale: 1, duration: .5, ease: 'back.out(2.1)', stagger: 0.07, immediateRender: false }, 'site+=' + 0.62);
        this.introTL.fromTo('.ticket', { autoAlpha: 0, y: 14 }, { autoAlpha: 1, y: 0, duration: .5, ease: 'power2.out', immediateRender: false }, 'site+=' + 1.05);

        $('#skipBtn').addEventListener('click', () => { this.introTL.progress(1, false); this.finishIntro(); });
        if ($('#replayBtn')) {
            $('#replayBtn').addEventListener('click', () => this.replayIntro());
        }
    }

    startCollageFloats() {
        this.killCollageFloats();
        this.collageAll.forEach((el, i) => {
            this.floatTweens.push(gsap.to(el.querySelector('.art svg, .art img'), {
                y: i % 2 ? 9 : -9, 
                rotation: i % 2 ? 1.8 : -1.8,
                duration: 1.9 + (i % 3) * .45, 
                yoyo: true, 
                repeat: -1,
                ease: 'sine.inOut', 
                delay: (i % 4) * .18
            }));
        });
    }

    killCollageFloats() {
        this.floatTweens.forEach(t => t.kill()); 
        this.floatTweens = [];
        gsap.set('.collage-card .art svg, .collage-card .art img', { y: 0, rotation: 0 });
    }

    finishIntro() {
        if (this.finished) return;
        this.finished = true;
        this.killCollageFloats();
        document.body.classList.remove('intro-on');
        document.body.classList.add('ready');
        $('#site').setAttribute('aria-hidden', 'false');
        gsap.set('#intro', { display: 'none' });
        gsap.to('#skipBtn', { autoAlpha: 0, duration: .25 });
        if ($('#replayBtn')) gsap.to('#replayBtn', { autoAlpha: 1, duration: .45, delay: .4 });
        makePara(this.chainPara);
        gsap.set(['.t-line', '.tagline', '.ticket', '.site-head', '.floater'],
            { clearProps: 'transform,opacity,visibility,filter' });
        if (window.location.hash === '#welcome') {
            toast('Welcome to Cartoon Universe!');
            history.replaceState(null, '', ' ');
        }
    }

    replayIntro() {
        this.finished = false;
        document.body.classList.add('intro-on');
        document.body.classList.remove('ready');
        $('#site').setAttribute('aria-hidden', 'true');
        gsap.set('#intro', { display: 'block' });
        gsap.set('#introStage', { scale: 1 });
        gsap.set('.card .art', { x: 0, y: 0, scaleX: 1, scaleY: 1, rotation: 0 });
        gsap.set('.card .art svg, .card .art img', { y: 0, rotation: 0 });
        gsap.set(['.t-line', '.tagline', '.ticket', '.site-head', '.floater'], { clearProps: 'all' });
        gsap.set(['.t-line', '.tagline', '.ticket', '.site-head'], { autoAlpha: 0 });
        gsap.set('.floater', { autoAlpha: 0 });
        gsap.set('#site', { scale: 1.1 });
        gsap.set('#replayBtn', { autoAlpha: 0 });
        gsap.set('#skipBtn', { autoAlpha: 1 });
        makePara(this.chainPara);
        this.introTL.restart();
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IntroAnimation;
}

// Also expose globally for main.js to use
window.IntroAnimation = IntroAnimation;