(() => {
  'use strict';
  const $ = s => document.querySelector(s);
  const $$ = s => [...document.querySelectorAll(s)];
  const vw = () => window.innerWidth, vh = () => window.innerHeight;
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Load star SVGs for the pop game
  let STAR = '', STAR_GOLD = '';
  async function loadStarSVGs() {
    try {
      const [starResp, starGoldResp] = await Promise.all([
        fetch('assets/svg/star-pop.svg'),
        fetch('assets/svg/star-pop-gold.svg')
      ]);
      STAR = await starResp.text();
      STAR_GOLD = await starGoldResp.text();
    } catch (e) {
      console.warn('Could not load star SVGs', e);
      // Fallback inline SVGs
      STAR = '<svg viewBox="0 0 100 100"><path d="M50 21 L59.5 44 L84.5 46 L65.5 62 L71.5 86 L50 73 L28.5 86 L34.5 62 L15.5 46 L40.5 44 Z" fill="#FFFDF7" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/><circle cx="43" cy="52" r="3.2" fill="#33261D"/><circle cx="57" cy="52" r="3.2" fill="#33261D"/><path d="M46 59 Q50 62.5 54 59" stroke="#33261D" stroke-width="2.6" fill="none" stroke-linecap="round"/></svg>';
      STAR_GOLD = '<svg viewBox="0 0 100 100"><path d="M50 21 L59.5 44 L84.5 46 L65.5 62 L71.5 86 L50 73 L28.5 86 L34.5 62 L15.5 46 L40.5 44 Z" fill="#FFC531" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/><circle cx="43" cy="52" r="3.2" fill="#33261D"/><circle cx="57" cy="52" r="3.2" fill="#33261D"/><path d="M46 59 Q50 62.5 54 59" stroke="#33261D" stroke-width="2.6" fill="none" stroke-linecap="round"/></svg>';
    }
  }
  const sparkSVG = c => `<svg viewBox="0 0 20 20"><path d="M10 1L12 8 19 10 12 12 10 19 8 12 1 10 8 8Z" fill="${c}"/></svg>`;

/* ---------- Error Handling ---------- */
window.CartoonUniverse = window.CartoonUniverse || {};
window.CartoonUniverse.showError = function(message, container) {
  const errorEl = document.createElement('div');
  errorEl.className = 'error-state';
  errorEl.innerHTML = `
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 16px; opacity: 0.7;">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="15" y1="9" x2="9" y2="15"></line>
      <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
    <h3 style="font-family: 'Luckiest Guy', cursive; color: var(--coral); margin-bottom: 8px;">Oops!</h3>
    <p style="color: var(--text-secondary);">${message}</p>
    <button class="btn-pill" style="margin-top: 16px;" onclick="this.parentElement.remove()">Dismiss</button>
  `;
  errorEl.style.cssText = 'padding: 24px; text-align: center; background: var(--bg-card); border: 2px solid var(--coral); border-radius: 16px; color: var(--text-primary);';
  
  if (container) {
    container.innerHTML = '';
    container.appendChild(errorEl);
  } else {
    toast(message, 'error');
  }
};

window.CartoonUniverse.showLoading = function(container) {
  if (!container) return;
  container.innerHTML = `
    <div class="skeleton-card" style="flex: 0 0 200px;">
      <div class="skeleton-poster"></div>
      <div class="skeleton-info">
        <div class="skeleton-title"></div>
        <div class="skeleton-meta"></div>
      </div>
    </div>
    <div class="skeleton-card" style="flex: 0 0 200px;">
      <div class="skeleton-poster"></div>
      <div class="skeleton-info">
        <div class="skeleton-title"></div>
        <div class="skeleton-meta"></div>
      </div>
    </div>
    <div class="skeleton-card" style="flex: 0 0 200px;">
      <div class="skeleton-poster"></div>
      <div class="skeleton-info">
        <div class="skeleton-title"></div>
        <div class="skeleton-meta"></div>
      </div>
    </div>
  `;
};

window.CartoonUniverse.handleApiError = function(error, container) {
  console.error('API Error:', error);
  let message = 'Unable to load content right now. Please try again.';
  if (error.name === 'TypeError' && error.message.includes('fetch')) {
    message = 'Network error. Please check your connection and try again.';
  } else if (error.message) {
    message = error.message;
  }
  window.CartoonUniverse.showError(message, container);
};

window.CartoonUniverse.fetchWithTimeout = async function(url, options = {}, timeout = 10000) {
  const controller = new AbortController();
  const id = setTimeout(() => controller.abort(), timeout);
  
  try {
    const response = await fetch(url, {
      ...options,
      signal: controller.signal
    });
    clearTimeout(id);
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    return await response.json();
  } catch (error) {
    clearTimeout(id);
    if (error.name === 'AbortError') {
      throw new Error('Request timed out. Please try again.');
    }
    throw error;
  }
};

  /* ---------- toast ---------- */
  const toastEl = $('#toast');
  let toastTO;
  function toast(msg, icon = 'star') {
    toastEl.innerHTML = (icon === 'play'
      ? '<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>'
      : '<svg viewBox="0 0 24 24"><path d="M12 2.5l2.6 5.9 6.4.6-4.9 4.2 1.5 6.3L12 16.3 6.4 19.5l1.5-6.3L3 9l6.4-.6z" fill="currentColor"/></svg>')
      + `<span>${msg}</span>`;
    toastEl.classList.add('show');
    clearTimeout(toastTO);
    toastTO = setTimeout(() => toastEl.classList.remove('show'), 2600);
  }

  /* ---------- parallax ---------- */
  let paraList = [];
  function makePara(entries) {
    paraList = entries.filter(e => e.el).map(e => ({
      depth: e.depth,
      qx: gsap.quickTo(e.el, 'x', { duration: .9, ease: 'power3.out' }),
      qy: gsap.quickTo(e.el, 'y', { duration: .9, ease: 'power3.out' })
    }));
  }
  if (matchMedia('(pointer:fine)').matches) {
    window.addEventListener('pointermove', e => {
      const nx = e.clientX / vw() - .5, ny = e.clientY / vh() - .5;
      paraList.forEach(p => { p.qx(nx * p.depth); p.qy(ny * p.depth * .7); });
    });
  }
  const sitePara = [
    { el: $('.sky'), depth: 12 }, { el: $('.hills'), depth: 6 }, { el: $('.hero'), depth: 8 },
    { el: $('.f-fox'), depth: 26 }, { el: $('.f-bunny'), depth: 20 },
    { el: $('.f-star'), depth: 16 }, { el: $('.f-balloon'), depth: 30 }
  ];

  /* ---------- ambient world (har page par) ---------- */
  function ambient() {
    if (reduced) return;
    gsap.to('#sunRays', { rotation: 360, svgOrigin: '60 60', duration: 80, repeat: -1, ease: 'none' });
    $$('.cloud').forEach((c, i) => {
      gsap.fromTo(c, { x: -vw() * 1.3 }, { x: vw() * 1.3, duration: 48 + i * 16, repeat: -1, ease: 'none', delay: -i * 13 });
    });
    [['.f-fox .bob', 12, -1.5, 2.6], ['.f-bunny .bob', 10, 2, 2.1],
    ['.f-star .bob', 14, 3, 2.9], ['.f-balloon .bob', 16, -2, 3.4]]
      .forEach(([sel, y, r, d]) => gsap.to(sel, { y, rotation: r, yoyo: true, repeat: -1, duration: d, ease: 'sine.inOut' }));
    const pw = $('#particles'), cols = ['#FF5A3C', '#12A594', '#FFC531', '#FFB4A2'];
    for (let i = 0; i < 14; i++) {
      const p = document.createElement('i');
      const s = gsap.utils.random(6, 11);
      p.style.width = p.style.height = s + 'px';
      p.style.left = gsap.utils.random(3, 96) + '%';
      p.style.top = gsap.utils.random(6, 86) + '%';
      if (i % 4 === 0) p.innerHTML = sparkSVG(cols[i % 4]);
      else p.style.background = cols[i % 4];
      pw.appendChild(p);
      gsap.to(p, { y: gsap.utils.random(-46, 46), x: gsap.utils.random(-34, 34), duration: gsap.utils.random(4, 7.5), yoyo: true, repeat: -1, ease: 'sine.inOut', delay: gsap.utils.random(0, 3) });
      gsap.to(p, { autoAlpha: .25, duration: gsap.utils.random(1.5, 3), yoyo: true, repeat: -1, ease: 'sine.inOut' });
    }
  }

  /* ---------- STAR POP (games.html) ---------- */
  let gEls = null, gTick = null, gSpawn = null, gScore = 0, gTime = 20, best = 0;
  try { best = +(localStorage.getItem('cu_starpop_best') || 0); } catch (e) { }

  function startGame() {
    if (!gEls) return;
    gScore = 0; gTime = 20;
    gEls.score.textContent = '0';
    gEls.time.textContent = '20s';
    gEls.time.classList.remove('urgent');
    gEls.intro.hidden = true; gEls.play.hidden = false;
    gEls.arena.innerHTML = '';
    clearInterval(gTick); clearInterval(gSpawn);
    gTick = setInterval(() => {
      gTime -= .1;
      if (gTime <= 0) { endGame(); return; }
      gEls.time.textContent = Math.ceil(gTime) + 's';
      gEls.time.classList.toggle('urgent', gTime < 5);
    }, 100);
    spawnStar();
    gSpawn = setInterval(spawnStar, 620);
  }
  function spawnStar() {
    if (!gEls || gEls.arena.querySelectorAll('.popstar').length > 9) return;
    const golden = Math.random() < .16;
    const el = document.createElement('button');
    el.className = 'popstar' + (golden ? ' golden' : '');
    el.setAttribute('aria-label', 'Pop the star');
    const size = gsap.utils.random(40, 58);
    el.style.width = el.style.height = size + 'px';
    el.style.left = gsap.utils.random(4, 86) + '%';
    el.innerHTML = golden ? STAR_GOLD : STAR;
    gEls.arena.appendChild(el);
    gsap.to(el, { y: -(gEls.arena.offsetHeight + 140), rotation: gsap.utils.random(-70, 70), duration: gsap.utils.random(2.4, 3.6), ease: 'none', onComplete: () => el.remove() });
    gsap.fromTo(el, { scale: 0 }, { scale: 1, duration: .25, ease: 'back.out(2)' });
  }
  function popStar(el) {
    if (el.dataset.dead || !gEls) return;
    el.dataset.dead = '1';
    gScore += el.classList.contains('golden') ? 3 : 1;
    gEls.score.textContent = gScore;
    gsap.killTweensOf(el);
    const r = el.getBoundingClientRect(), ar = gEls.arena.getBoundingClientRect();
    const x = r.left + r.width / 2 - ar.left, y = r.top + r.height / 2 - ar.top;
    for (let i = 0; i < 6; i++) {
      const p = document.createElement('i');
      p.className = 'pp';
      p.style.left = x + 'px'; p.style.top = y + 'px';
      p.style.background = ['#FF5A3C', '#12A594', '#FFC531'][i % 3];
      gEls.arena.appendChild(p);
      const a = (i / 6) * Math.PI * 2 + gsap.utils.random(-.4, .4), d = gsap.utils.random(28, 66);
      gsap.to(p, { x: Math.cos(a) * d, y: Math.sin(a) * d, autoAlpha: 0, scale: .4, duration: .5, ease: 'power2.out', onComplete: () => p.remove() });
    }
    gsap.timeline()
      .to(el, { scale: 1.6, rotation: '+=40', duration: .12, ease: 'power2.out' })
      .to(el, { scale: 0, autoAlpha: 0, duration: .18, ease: 'power2.in', onComplete: () => el.remove() });
  }
  function endGame() {
    if (!gEls) return;
    clearInterval(gTick); clearInterval(gSpawn);
    gTime = 0; gEls.time.textContent = '0s';
    const isBest = gScore > best && gScore > 0;
    if (isBest) { best = gScore; try { localStorage.setItem('cu_starpop_best', best); } catch (e) { } gEls.best.textContent = best; }
    gsap.to(gEls.arena.querySelectorAll('.popstar'), { autoAlpha: 0, scale: .3, duration: .3, onComplete: () => gEls.arena.querySelectorAll('.popstar').forEach(p => p.remove()) });
    const box = document.createElement('div');
    box.className = 'g-result';
    box.innerHTML = `<div><p class="g-final">${gScore}</p><p class="g-msg">${isBest ? 'New personal best!' : 'The stars got away — try again!'}</p><button class="btn-primary" id="gAgain">PLAY AGAIN</button></div>`;
    gEls.arena.appendChild(box);
    gsap.fromTo(box, { autoAlpha: 0, scale: .9 }, { autoAlpha: 1, scale: 1, duration: .35, ease: 'back.out(1.7)' });
  }

  /* ---------- site-wide delegated clicks ---------- */
  document.addEventListener('click', e => {
    const ep = e.target.closest('.ep-row');
    if (ep) { toast(`Now playing — ${ep.dataset.title}`, 'play'); return; }
    const w = e.target.closest('[data-watch]');
    if (w) { toast(`Rolling soon — ${w.dataset.watch} is in production`); return; }
    const start = e.target.closest('#gStart, #gAgain');
    if (start) { startGame(); return; }
    const pop = e.target.closest('.popstar');
    if (pop) popStar(pop);
  });

  /* ---------- little delights ---------- */
  const bigTitle = $('#bigTitle');
  if (bigTitle) {
    bigTitle.addEventListener('click', () => {
      if (!finished) return;
      gsap.fromTo(bigTitle, { scale: .94, rotation: -1 }, { scale: 1, rotation: 0, duration: .8, ease: 'elastic.out(1.1,.4)' });
      const r = bigTitle.getBoundingClientRect();
      const cx = r.left + r.width / 2, cy = r.top + r.height / 2;
      for (let i = 0; i < 10; i++) {
        const s = document.createElement('span');
        s.className = 'burst-bit';
        s.style.left = cx + 'px'; s.style.top = cy + 'px';
        if (i % 3 === 0) s.innerHTML = i % 2 === 0 ? STAR_GOLD : STAR;
        else s.style.background = ['#FF5A3C', '#12A594', '#FFC531'][i % 3];
        document.body.appendChild(s);
        const a = (i / 10) * Math.PI * 2 + gsap.utils.random(-.3, .3), d = gsap.utils.random(90, 190);
        gsap.to(s, { x: Math.cos(a) * d, y: Math.sin(a) * d - 30, rotation: gsap.utils.random(-180, 180), autoAlpha: 0, scale: gsap.utils.random(.4, .9), duration: gsap.utils.random(.7, 1.1), ease: 'power2.out', onComplete: () => s.remove() });
      }
    });
  }
  $$('.floater').forEach(f =>
    f.addEventListener('click', () => {
      gsap.fromTo(f, { scale: .88, rotation: -4 }, { scale: 1, rotation: 0, duration: .8, ease: 'elastic.out(1.1,.45)' });
    }));

  /* =================================================================
     CINEMATIC INTRO — only on index.html (detected by #intro presence)
     ================================================================= */
  const hasIntro = !!document.getElementById('intro');
  let introTL = null, finished = !hasIntro;
  let chainPara = [], collageAll = [], floatTweens = [];

  function startCollageFloats() {
    killCollageFloats();
    collageAll.forEach((el, i) => {
      floatTweens.push(gsap.to(el.querySelector('.art svg, .art img'), {
        y: i % 2 ? 9 : -9, rotation: i % 2 ? 1.8 : -1.8,
        duration: 1.9 + (i % 3) * .45, yoyo: true, repeat: -1,
        ease: 'sine.inOut', delay: (i % 4) * .18
      }));
    });
  }
  function killCollageFloats() {
    floatTweens.forEach(t => t.kill()); floatTweens = [];
    gsap.set('.collage-card .art svg, .collage-card .art img', { y: 0, rotation: 0 });
  }

  function finishIntro() {
    if (finished) return;
    finished = true;
    killCollageFloats();
    document.body.classList.remove('intro-on');
    document.body.classList.add('ready');
    $('#site').setAttribute('aria-hidden', 'false');
    gsap.set('#intro', { display: 'none' });
    gsap.to('#skipBtn', { autoAlpha: 0, duration: .25 });
    if ($('#replayBtn')) gsap.to('#replayBtn', { autoAlpha: 1, duration: .45, delay: .4 });
    makePara(sitePara);
    gsap.set(['.t-line', '.tagline', '.ticket', '.site-head', '.floater'],
      { clearProps: 'transform,opacity,visibility,filter' });
    // Check for flash message in URL hash
    if (window.location.hash === '#welcome') {
      toast('Welcome to Cartoon Universe!');
      history.replaceState(null, '', ' ');
    }
  }

  function buildIntro() {
    const chain = $$('.chain-card');
    chain.forEach((c, i) => gsap.set(c, { xPercent: -50, yPercent: -50, autoAlpha: 0, zIndex: 1 + i }));
    const sideEls = $$('.collage-card:not(.main-card)');
    sideEls.forEach((c, i) => gsap.set(c, { xPercent: -50, yPercent: -50, autoAlpha: 0, zIndex: 20 + i }));
    const mainCard = $('.collage-card.main-card');
    gsap.set(mainCard, { xPercent: -50, yPercent: -50, autoAlpha: 0, zIndex: 50 });
    collageAll = [mainCard, ...sideEls];
    const cardByKey = k => $('#collageLayer').querySelector(`.collage-card[data-scene="${k}"]`);
    chainPara = chain.map((c, i) => ({ el: c.querySelector('.art'), depth: 8 + (i % 3) * 7 }));

    gsap.set('#whitePanel', { xPercent: -50, yPercent: -50, scale: 0, rotation: -10 });
    gsap.set('#brandMark', { xPercent: -50, yPercent: -50 });
    gsap.set(['.t-line', '.tagline', '.ticket', '.site-head'], { autoAlpha: 0 });
    gsap.set('.floater', { autoAlpha: 0 });
    gsap.set('#site', { scale: 1.1 });

    const W = vw(), H = vh(), P = W / H < .85;
    const X = f => (f - .5) * W, Y = f => (f - .5) * H;
    introTL = gsap.timeline({ delay: .25, onComplete: finishIntro });

    /* PHASE 1 — the card chain */
    const stepX = P ? .085 : .115, stepY = P ? .125 : .107;
    const rots = [-7, 6, -5, 7, -6, 5, -4];
    const pos = chain.map((c, i) => ({
      fx: .84 - i * stepX, fy: (P ? .13 : .16) + i * stepY,
      tx: X(.84 - i * stepX), ty: Y((P ? .13 : .16) + i * stepY)
    }));
    chain.forEach((card, i) => {
      const p = pos[i], fromLeft = i % 2 === 0;
      introTL.set(card, {
        x: fromLeft ? -W * 1.15 : p.tx, y: fromLeft ? p.ty : H * 1.2,
        rotation: rots[i] * 2.3, scale: .92, autoAlpha: 1
      }, 0);
      introTL.to(card, { x: p.tx, y: p.ty, rotation: rots[i], scale: 1, duration: .95, ease: 'power3.out' }, .3 + i * .4);
      introTL.fromTo(card.querySelector('.art svg, .art img'),
        { scaleX: 1.16, scaleY: .9 },
        { scaleX: 1, scaleY: 1, duration: .55, ease: 'elastic.out(1,.45)', immediateRender: false },
        .3 + i * .4 + .5);
    });

    /* PHASE 2 — white panel wash */
    introTL.addLabel('scatter', 3.9);
    chain.forEach((card, i) => {
      const p = pos[i];
      introTL.to(card, {
        x: p.tx + (p.fx - .5) * W * 1.6, y: p.ty + (p.fy - .5) * H * 1.6,
        rotation: rots[i] + (i % 2 ? 30 : -36), scale: .8, duration: .9, ease: 'power2.in'
      }, 'scatter+=' + i * .03);
      introTL.to(card, { autoAlpha: 0, duration: .32, ease: 'none' }, 'scatter+=' + (.5 + i * .02));
    });
    introTL.fromTo('#introStage', { scale: 1 }, { scale: 1.06, duration: 1, ease: 'power2.inOut', immediateRender: false }, 'scatter+=.15');
    introTL.fromTo('#whitePanel', { scale: 0, rotation: -10 },
      { scale: 1, rotation: 0, duration: .72, ease: 'power4.in', immediateRender: false }, 'scatter+=.5');
    introTL.addLabel('white', 'scatter+=1.22');
    introTL.set('#introStage', { scale: 1 }, 'white');
    introTL.fromTo('#flash', { autoAlpha: 0 }, { autoAlpha: .95, duration: .12, ease: 'power2.in', immediateRender: false }, 'white-=.12');
    introTL.to('#flash', { autoAlpha: 0, duration: .55, ease: 'power2.out' }, 'white+=.04');
    introTL.fromTo('#brandMark', { autoAlpha: 0, scale: .55, y: 14 },
      { autoAlpha: 1, scale: 1, y: 0, duration: .5, ease: 'back.out(2)', immediateRender: false }, 'white+=.15');
    introTL.to('#brandMark', { autoAlpha: 0, scale: .85, duration: .28, ease: 'power2.in' }, 'white+=.62');

    /* PHASE 3 — collage reveal */
    const defs = P ? [
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
      top: { sx: X(.5), sy: -H * .85 }, left: { sx: -W * .8, sy: Y(.45) },
      right: { sx: W * .8, sy: Y(.45) }, bl: { sx: -W * .75, sy: H * .8 },
      br: { sx: W * .75, sy: H * .8 }, bottom: { sx: X(.5), sy: H * .95 }
    };
    const sides = defs.map(d => {
      const el = cardByKey(d.key);
      return { ...d, el, tx: X(d.fx), ty: Y(d.fy), ...offs[d.dir] };
    });

    introTL.addLabel('reveal', 'white+=1.02');
    sides.forEach((d, i) => {
      introTL.set(d.el, { x: d.sx, y: d.sy, rotation: d.rot * 2.2, scale: 1.28, autoAlpha: 1 }, 'reveal');
      introTL.to(d.el, { x: d.tx * .45, y: d.ty * .45, scale: 1.16, duration: .5, ease: 'power3.out' }, 'reveal+=' + (.05 + i * .11));
      introTL.to(d.el, { x: d.tx, y: d.ty, scale: d.s, rotation: d.rot, rotationX: d.rx || 0, rotationY: d.ry || 0, duration: .55, ease: 'back.out(1.5)' }, 'reveal+=' + (.5 + i * .11));
    });
    introTL.fromTo(mainCard, { scale: 0, rotation: 12, x: 0, y: 0, autoAlpha: 1 },
      { scale: 1, rotation: -1.5, duration: .7, ease: 'back.out(1.6)', immediateRender: false }, 'reveal+=.78');

    introTL.addLabel('formed', 'reveal+=1.5');
    introTL.call(startCollageFloats, null, 'formed');
    introTL.to({}, { duration: .85 }, 'formed');

    /* PHASE 4 — explosion */
    introTL.addLabel('boom', 'formed+=.85');
    introTL.call(killCollageFloats, null, 'boom');
    sides.forEach((d, i) => {
      introTL.to(d.el, {
        x: d.tx + (d.fx - .5) * W * 2.1, y: d.ty + (d.fy - .5) * H * 2.1,
        rotation: d.rot + (i % 2 ? 46 : -38), scale: .45,
        duration: .95 + (i % 3) * .14, ease: 'power2.in'
      }, 'boom');
      introTL.to(d.el, { autoAlpha: 0, duration: .3, ease: 'none' }, 'boom+=' + (.55 + (i % 3) * .07));
    });
    introTL.to(mainCard, { scale: 2.5, autoAlpha: 0, rotation: 10, duration: .55, ease: 'power2.in' }, 'boom+=.06');
    introTL.to('#whitePanel', { scale: 1.25, autoAlpha: 0, duration: .8, ease: 'power2.inOut' }, 'boom+=.12');
    introTL.to('#introBg', { autoAlpha: 0, duration: .8, ease: 'power2.inOut' }, 'boom+=.12');
    introTL.fromTo('#flash', { autoAlpha: 0 }, { autoAlpha: .55, duration: .1, immediateRender: false }, 'boom');
    introTL.to('#flash', { autoAlpha: 0, duration: .5, ease: 'power2.out' }, 'boom+=.12');
    introTL.fromTo('#site', { scale: 1.1 }, { scale: 1, duration: 1.3, ease: 'power3.out', immediateRender: false }, 'boom');

    /* PHASE 5 — site reveal */
    introTL.addLabel('site', 'boom+=.55');
    introTL.fromTo('.floater', { autoAlpha: 0, y: 46, scale: .7 },
      { autoAlpha: 1, y: 0, scale: 1, duration: .8, ease: 'back.out(1.8)', stagger: .09, immediateRender: false }, 'boom+=.3');
    introTL.fromTo('.t-line', { autoAlpha: 0, y: 56, scale: 1.45, filter: 'blur(10px)' },
      { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 1, ease: 'power4.out', stagger: .14, immediateRender: false }, 'site');
    introTL.fromTo('.site-head', { autoAlpha: 0, y: -18 }, { autoAlpha: 1, y: 0, duration: .6, ease: 'power2.out', immediateRender: false }, 'site+=.35');
    introTL.fromTo('.tagline', { autoAlpha: 0, y: 22 }, { autoAlpha: 1, y: 0, duration: .7, ease: 'power3.out', immediateRender: false }, 'site+=.5');
    introTL.fromTo('.nav-btn', { autoAlpha: 0, y: 26, scale: .82 },
      { autoAlpha: 1, y: 0, scale: 1, duration: .5, ease: 'back.out(2.1)', stagger: .07, immediateRender: false }, 'site+=.62');
    introTL.fromTo('.ticket', { autoAlpha: 0, y: 14 }, { autoAlpha: 1, y: 0, duration: .5, ease: 'power2.out', immediateRender: false }, 'site+=1.05');

    $('#skipBtn').addEventListener('click', () => { introTL.progress(1, false); finishIntro(); });
    if ($('#replayBtn')) {
      $('#replayBtn').addEventListener('click', () => {
        finished = false;
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
        makePara(chainPara);
        introTL.restart();
      });
    }
  }

  /* ---------- page entrance (characters, shows, episodes, games, about) ---------- */
  function pageEntrance() {
    document.body.classList.add('ready');
    if (reduced) return;
    gsap.fromTo('.site-head', { autoAlpha: 0, y: -18 }, { autoAlpha: 1, y: 0, duration: .6, ease: 'power2.out' });
    gsap.fromTo('.floater', { autoAlpha: 0, y: 40, scale: .7 },
      { autoAlpha: 1, y: 0, scale: 1, duration: .8, ease: 'back.out(1.8)', stagger: .08, delay: .15 });
    gsap.fromTo('.page-card', { autoAlpha: 0, y: 56, scale: .95, rotation: -1.2 },
      { autoAlpha: 1, y: 0, scale: 1, rotation: 0, duration: .8, ease: 'power3.out', delay: .05 });
    const items = $$('[data-stagger] > *');
    if (items.length) {
      gsap.fromTo(items, { y: 18, autoAlpha: 0 }, {
        y: 0, autoAlpha: 1, duration: .45, stagger: .06, ease: 'power2.out', delay: .4,
        onComplete() { gsap.set(items, { clearProps: 'transform,opacity,visibility' }); }
      });
    }
    makePara(sitePara);
  }

  /* ---------- boot ---------- */
  let booted = false;
  async function init() {
    if (booted) return;
    booted = true;
    await loadStarSVGs();
    ambient();
    if ($('#gameArena')) {
      gEls = {
        intro: $('#gameIntro'), play: $('#gamePlay'),
        score: $('#gScore'), time: $('#gTime'),
        arena: $('#gameArena'), best: $('#gBest')
      };
      gEls.best.textContent = best;
    }
    if (hasIntro) {
      buildIntro();
      makePara(chainPara);
      gsap.to('#skipBtn', { autoAlpha: 1, delay: 1.2, duration: .4 });
      if (reduced) { introTL.progress(1, false); finishIntro(); }
    } else {
      pageEntrance();
    }
  }
  if (hasIntro) {
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(init);
    setTimeout(init, 1400);
  } else {
    init();
  }
})();

});





document.addEventListener("DOMContentLoaded", function () {

  const fileInput = document.getElementById("picture");
  const avatarPreview = document.getElementById("avatarPreview");
  const avatarImage = document.getElementById("avatarImage");

  if (!fileInput || !avatarPreview || !avatarImage) {
    return;
  }

  fileInput.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) {
      return;
    }

    // Allowed types
    const allowedTypes = [
      "image/jpeg",
      "image/png",
      "image/webp"
    ];

    if (!allowedTypes.includes(file.type)) {
      showFormMessage("Please select a JPG, PNG or WEBP image.", "error");
      this.value = "";
      return;
    }

    if (file.size > 2 * 1024 * 1024) {
      showFormMessage("Image must be smaller than 2MB.", "error");
      this.value = "";
      return;
    }



    // Create preview
    const reader = new FileReader();

    reader.onload = function (event) {

      avatarImage.src = event.target.result;

      // Selected image state
      avatarPreview.classList.add("has-image");
    };

reader.readAsDataURL(file);
  });

  // Mobile Menu Toggle
  const mobileMenuToggle = $('#mobileMenuToggle');
  const mainNav = $('#mainNav');
  
  if (mobileMenuToggle && mainNav) {
    mobileMenuToggle.addEventListener('click', function() {
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', !isExpanded);
      mainNav.classList.toggle('open');
      
      // Prevent body scroll when menu is open
      if (!isExpanded) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    });
    
    // Close menu when clicking a nav link
    $$('#mainNav .nav-btn').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenuToggle.setAttribute('aria-expanded', 'false');
        mainNav.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!mainNav.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
        mobileMenuToggle.setAttribute('aria-expanded', 'false');
        mainNav.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
    
    // Close on escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mainNav.classList.contains('open')) {
        mobileMenuToggle.setAttribute('aria-expanded', 'false');
        mainNav.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
  }

});

