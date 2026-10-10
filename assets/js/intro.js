/**
 * Intro Animation Module
 * 
 * Handles the cinematic intro animation sequence using GSAP.
 * Configured via intro-config.js
 */

// Utility functions
const $ = s => document.querySelector(s);
const $$ = s => [...document.querySelectorAll(s)];
const vw = () => window.innerWidth, vh = () => window.innerHeight;
const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

// Check if GSAP is available
if (typeof gsap === 'undefined') {
    console.warn('GSAP not loaded, intro animation disabled');
    document.body.classList.remove('intro-on');
    document.body.classList.add('ready');
    $('#site')?.setAttribute('aria-hidden', 'false');
    gsap = { timeline: () => ({ to: () => {}, fromTo: () => {}, set: () => {}, addLabel: () => {}, call: () => {}, progress: () => {}, restart: () => {} }) };
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
        this.cardByKey = null;
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
        
        this.chain = $$('.chain-card');
        this.sides = $$('.collage-card:not(.main-card)');
        this.mainCard = $('.collage-card.main-card');
        
        if (this.chain.length === 0 || this.sides.length === 0 || !this.mainCard) {
            console.warn('Intro elements not found, skipping animation');
            this.finishIntro();
            return;
        }
        
        this.buildIntro();
        
        // Set up parallax for chain cards
        this.chainPara = this.chain.map((c, i) => ({ 
            el: c.querySelector('.art'), 
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
        const sideEls = $$('.collage-card:not(.main-card)');
        const mainCard = $('.collage-card.main-card');
        
        chain.forEach((c, i) => gsap.set(c, { xPercent: -50, yPercent: -50, autoAlpha: 0, zIndex: 1 + i }));
        sideEls.forEach((c, i) => gsap.set(c, { xPercent: -50, yPercent: -50, autoAlpha: 0, zIndex: 20 + i }));
        gsap.set(mainCard, { xPercent: -50, yPercent: -50, autoAlpha: 0, zIndex: 50 });
        
        this.collageAll = [mainCard, ...sideEls];
        
        const cardByKey = k => $('#collageLayer').querySelector(`.collage-card[data-scene="${k}"]`);
        this.chainPara = chain.map((c, i) => ({ el: c.querySelector('.art'), depth: 8 + (i % 3) * 7 }));

        gsap.set('#whitePanel', { xPercent: -50, yPercent: -50, scale: 0, rotation: -10 });
        gsap.set('#brandMark', { xPercent: -50, yPercent: -50 });
        gsap.set(['.t-line', '.tagline', '.ticket', '.site-head'], { autoAlpha: 0 });
        gsap.set('.floater', { autoAlpha: 0 });
        gsap.set('#site', { scale: 1.1 });

        this.W = vw(), this.H = vh(), this.P = this.W / this.H < .85;
        this.X = f => (f - .5) * this.W, this.Y = f => (f - .5) * this.H;
        this.introTL = gsap.timeline({ delay: .25, onComplete: this.finishIntro });

        /* PHASE 1 — the card chain */
        const stepX = this.P ? .085 : .115, stepY = this.P ? .125 : .107;
        this.rots = [-7, 6, -5, 7, -6, 5, -4];
        this.chainPos = chain.map((c, i) => ({
            fx: .84 - i * stepX, fy: (this.P ? .13 : .16) + i * stepY,
            tx: this.X(.84 - i * stepX), ty: this.Y((this.P ? .13 : .16) + i * stepY)
        });
        
        const chainArray = Array.from(chain);
        chainArray.forEach((card, i) => {
            const p = this.chainPos[i], fromLeft = i % 2 === 0;
            this.introTL.set(card, {
                x: fromLeft ? -this.W * 1.15 : p.tx, y: fromLeft ? p.ty : this.H * 1.2,
                rotation: this.rots[i] * 2.3, scale: .92, autoAlpha: 1
            }, 0);
            this.introTL.to(card, { x: p.tx, y: p.ty, rotation: this.rots[i], scale: 1, duration: .95, ease: 'power3.out' }, .3 + i * .4);
            this.introTL.fromTo(card.querySelector('.art svg, .art img'),
                { scaleX: 1.16, scaleY: .9 },
                { scaleX: 1, scaleY: 1, duration: .55, ease: 'elastic.out(1,.45)', immediateRender: false },
                .3 + i * .4 + .5);
        });

        /* PHASE 2 — white panel wash / scatter */
        this.introTL.addLabel('scatter', INTRO_TIMING.scatter.label);
        chainArray.forEach((card, i) => {
            const p = this.chainPos[i];
            this.introTL.to(card, {
                x: p.tx + (p.fx - .5) * this.W * 1.6, y: p.ty + (p.fy - .5) * this.H * 1.6,
                rotation: this.rots[i] + (i % 2 ? 30 : -36), scale: .8, duration: INTRO_TIMING.scatter.duration, ease: 'power2.in'
            }, 'scatter+=' + i * INTRO_TIMING.scatter.stagger);
            this.introTL.to(card, { autoAlpha: 0, duration: INTRO_TIMING.scatter.fadeDuration, ease: 'none' }, 'scatter+=' + (INTRO_TIMING.scatter.fadeDelay + i * .02));
        });
        this.introTL.fromTo('#introStage', { scale: 1 }, { scale: 1.06, duration: 1, ease: 'power2.inOut', immediateRender: false }, 'scatter+=.15');
        this.introTL.fromTo('#whitePanel', { scale: 0, rotation: -10 },
            { scale: 1, rotation: 0, duration: INTRO_TIMING.white.panelDuration, ease: 'power4.in', immediateRender: false }, 'scatter+=.5');
        this.introTL.addLabel('white', 'scatter+=' + INTRO_TIMING.white.delay);
        this.introTL.set('#introStage', { scale: 1 }, 'white');
        this.introTL.fromTo('#flash', { autoAlpha: 0 }, { autoAlpha: .95, duration: INTRO_TIMING.white.flashIn, ease: 'power2.in', immediateRender: false }, 'white-=' + INTRO_TIMING.white.flashIn);
        this.introTL.to('#flash', { autoAlpha: 0, duration: INTRO_TIMING.white.flashOut, ease: 'power2.out' }, 'white+=' + INTRO_TIMING.white.flashOut);
        this.introTL.fromTo('#brandMark', { autoAlpha: 0, scale: .55, y: 14 },
            { autoAlpha: 1, scale: 1, y: 0, duration: INTRO_TIMING.white.brandMarkDuration, ease: 'back.out(2)', immediateRender: false }, 'white+=' + INTRO_TIMING.white.brandMarkDelay);
        this.introTL.to('#brandMark', { autoAlpha: 0, scale: .85, duration: INTRO_TIMING.white.brandMarkFadeDuration, ease: 'power2.in' }, 'white+=' + INTRO_TIMING.white.brandMarkFadeDelay);

        /* PHASE 3 — collage reveal */
        // Build sides array from actual DOM elements
        const collageCards = $$('.collage-card:not(.main-card)');
        this.sides = Array.from(collageCards).map(card => {
            const scene = card.dataset.scene;
            const isLandscape = card.classList.contains('land');
            return { key: scene, landscape: isLandscape, el: card };
        });
        
        const cardByKey = k => $('#collageLayer').querySelector(`.collage-card[data-scene="${k}"]`);
        
        // Define positions for collage cards (matching original layout)
        const defs = this.P ? [
            { key: 'star', fx: .5, fy: .11, rot: 3.5, s: .78, dir: 'top' },
            { key: 'bunny', fx: .17, fy: .33, rot: -8, s: .85, dir: 'left' },
            { key: 'cat', fx: .83, fy: .35, rot: 7, s: .85, dir: 'right' },
            { key: 'icecream', fx: .26, fy: .67, rot: -5, s: .85, dir: 'bl' },
            { key: 'controller', fx: .74, fy: .70, rot: 5, s: .85, dir: 'br' },
            { key: 'rainbow', fx: .5, fy: .87, rot: -3, s: .74, dir: 'bottom' }
        ] : [
            { key: 'rainbow', fx: .5, fy: .155, rot: 3.5, s: .9, dir: 'top', rx: 6 },
            { key: 'bunny', fx: .215, fy: .40, rot: -7, s: .9, dir: 'left', ry: 8 },
            { key: 'cat', fx: .785, fy: .41, rot: 6, s: .92, dir: 'right', ry: -8 },
            { key: 'star', fx: .30, fy: .735, rot: -4.5, s: .86, dir: 'bl' },
            { key: 'controller', fx: .70, fy: .755, rot: 5, s: .88, dir: 'br' },
            { key: 'burger', fx: .51, fy: .875, rot: -3, s: .76, dir: 'bottom' }
        ];
        const offs = {
            top: { sx: this.X(.5), sy: -this.H * .85 }, left: { sx: -this.W * .8, sy: this.Y(.45) },
            right: { sx: this.W * .8, sy: this.Y(.45) }, bl: { sx: -this.W * .75, sy: this.H * .8 },
            br: { sx: this.W * .75, sy: this.H * .8 }, bottom: { sx: this.X(.5), sy: this.H * .95 }
        };
        this.sides = defs.map(d => {
            const el = cardByKey(d.key);
            return { ...d, el, tx: this.X(d.fx), ty: this.Y(d.fy), ...offs[d.dir] };
        });

        this.introTL.addLabel('reveal', 'white+=' + INTRO_TIMING.reveal.delay);
        this.sides.forEach((d, i) => {
            this.introTL.set(d.el, { x: d.sx, y: d.sy, rotation: d.rot * 2.2, scale: 1.28, autoAlpha: 1 }, 'reveal');
            this.introTL.to(d.el, { x: d.tx * .45, y: d.ty * .45, scale: 1.16, duration: INTRO_TIMING.reveal.initialDuration, ease: 'power3.out' }, 'reveal+=' + (.05 + i * INTRO_TIMING.reveal.stagger));
            this.introTL.to(d.el, { x: d.tx, y: d.ty, scale: d.s, rotation: d.rot, rotationX: d.rx || 0, rotationY: d.ry || 0, duration: INTRO_TIMING.reveal.settleDuration, ease: 'back.out(1.5)' }, 'reveal+=' + (.5 + i * INTRO_TIMING.reveal.stagger));
        });
        this.introTL.fromTo(this.mainCard, { scale: 0, rotation: 12, x: 0, y: 0, autoAlpha: 1 },
            { scale: 1, rotation: -1.5, duration: .7, ease: 'back.out(1.6)', immediateRender: false }, 'reveal+=' + INTRO_TIMING.reveal.mainCardDelay);

        this.introTL.addLabel('formed', 'reveal+=' + INTRO_TIMING.reveal.formedDelay);
        this.introTL.call(this.startCollageFloats, null, 'formed');
        this.introTL.to({}, { duration: INTRO_TIMING.reveal.floatDuration }, 'formed');

        /* PHASE 4 — explosion */
        this.introTL.addLabel('boom', 'formed+=' + INTRO_TIMING.boom.delay);
        this.introTL.call(this.killCollageFloats, null, 'boom');
        this.sides.forEach((d, i) => {
            this.introTL.to(d.el, {
                x: d.tx + (d.fx - .5) * this.W * 2.1, y: d.ty + (d.fy - .5) * this.H * 2.1,
                rotation: d.rot + (i % 2 ? 46 : -38), scale: .45,
                duration: INTRO_TIMING.boom.duration + (i % 3) * .14, ease: 'power2.in'
            }, 'boom');
            this.introTL.to(d.el, { autoAlpha: 0, duration: .3, ease: 'none' }, 'boom+=' + (INTRO_TIMING.boom.fadeDelay + (i % 3) * INTRO_TIMING.boom.fadeStagger));
        });
        this.introTL.to(this.mainCard, { scale: 2.5, autoAlpha: 0, rotation: 10, duration: INTRO_TIMING.boom.mainCardDuration, ease: 'power2.in' }, 'boom+=' + INTRO_TIMING.boom.fadeDelay);
        this.introTL.to('#whitePanel', { scale: 1.25, autoAlpha: 0, duration: INTRO_TIMING.boom.panelDuration, ease: 'power2.inOut' }, 'boom+=' + INTRO_TIMING.boom.fadeDelay);
        this.introTL.to('#introBg', { autoAlpha: 0, duration: INTRO_TIMING.boom.bgDuration, ease: 'power2.inOut' }, 'boom+=' + INTRO_TIMING.boom.fadeDelay);
        this.introTL.fromTo('#flash', { autoAlpha: 0 }, { autoAlpha: .55, duration: INTRO_TIMING.boom.flashDuration, immediateRender: false }, 'boom');
        this.introTL.to('#flash', { autoAlpha: 0, duration: INTRO_TIMING.boom.flashOut, ease: 'power2.out' }, 'boom+=' + INTRO_TIMING.boom.flashOut);
        this.introTL.fromTo('#site', { scale: 1.1 }, { scale: 1, duration: INTRO_TIMING.boom.siteScaleDuration, ease: 'power3.out', immediateRender: false }, 'boom');

        /* PHASE 5 — site reveal */
        this.introTL.addLabel('site', 'boom+=' + INTRO_TIMING.site.delay);
        this.introTL.fromTo('.floater', { autoAlpha: 0, y: 46, scale: .7 },
            { autoAlpha: 1, y: 0, scale: 1, duration: .8, ease: 'back.out(1.8)', stagger: INTRO_TIMING.site.floaterStagger, immediateRender: false }, 'boom+=' + INTRO_TIMING.site.floaterDelay);
        this.introTL.fromTo('.t-line', { autoAlpha: 0, y: 56, scale: 1.45, filter: 'blur(10px)' },
            { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 1, ease: 'power4.out', stagger: INTRO_TIMING.site.titleStagger, immediateRender: false }, 'site+=' + INTRO_TIMING.site.titleDelay);
        this.introTL.fromTo('.site-head', { autoAlpha: 0, y: -18 }, { autoAlpha: 1, y: 0, duration: .6, ease: 'power2.out', immediateRender: false }, 'site+=' + INTRO_TIMING.site.headerDelay);
        this.introTL.fromTo('.tagline', { autoAlpha: 0, y: 22 }, { autoAlpha: 1, y: 0, duration: .7, ease: 'power3.out', immediateRender: false }, 'site+=' + INTRO_TIMING.site.taglineDelay);
        this.introTL.fromTo('.nav-btn', { autoAlpha: 0, y: 26, scale: .82 },
            { autoAlpha: 1, y: 0, scale: 1, duration: .5, ease: 'back.out(2.1)', stagger: INTRO_TIMING.site.navStagger, immediateRender: false }, 'site+=' + INTRO_TIMING.site.navDelay);
        this.introTL.fromTo('.ticket', { autoAlpha: 0, y: 14 }, { autoAlpha: 1, y: 0, duration: .5, ease: 'power2.out', immediateRender: false }, 'site+=' + INTRO_TIMING.site.ticketDelay);

        $('#skipBtn').addEventListener('click', () => { this.introTL.progress(1, false); this.finishIntro(); });
        if ($('#replayBtn')) {
            $('#replayBtn').addEventListener('click', () => this.replayIntro());
        }
    }

    startCollageFloats() {
        this.killCollageFloats();
        this.collageAll.forEach((el, i) => {
            this.floatTweens.push(gsap.to(el.querySelector('.art svg, .art img'), {
                y: i % 2 ? 9 : -9, rotation: i % 2 ? 1.8 : -1.8,
                duration: 1.9 + (i % 3) * .45, yoyo: true, repeat: -1,
                ease: 'sine.inOut', delay: (i % 4) * .18
            }));
        });
    }

    killCollageFloats() {
        this.floatTweens.forEach(t => t.kill()); this.floatTweens = [];
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

// Initialize intro animation when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (typeof IntroAnimation !== 'undefined') {
        window.introAnimation = new IntroAnimation();
        window.introAnimation.init();
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IntroAnimation;
}