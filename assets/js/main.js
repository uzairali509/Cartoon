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
  };

  /* ---------- parallax ---------- */
  let paraList = [];
  function makePara(entries) {
    paraList = entries.filter(e => e.el).map(e => ({ 
      el: e.el, 
      depth: 8 + (e.index % 3) * 7 
    }));
  }
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) { 
    makePara([]); 
  }
  window.addEventListener('pointermove', e => {
    const nx = e.clientX / vw() - .5, ny = e.clientY / vh() - .5;
    paraList.forEach(p => { p.qx(nx * p.depth); p.qy(ny * p.depth * .7); });
  });
  const sitePara = [
    { el: $('.sky'), depth: 12 }, { el: $('.hills'), depth: 6 }, { el: $('.hero'), depth: 8 },
    { el: $('.f-fox'), depth: 26 }, { el: $('.f-bunny'), depth: 20 },
    { el: $('.f-star'), depth: 16 }, { el: $('.f-balloon'), depth: 30 }
  ];

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
    initHomePage();
  }
  if (hasIntro) {
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(init);
    setTimeout(init, 1400);
  } else {
    init();
  }
})();

/* ---------- Home Page Animations ---------- */
function initHomePage() {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  // Scroll Reveal for sections
  const revealSections = document.querySelectorAll('.reveal-section');
  if (revealSections.length > 0 && !reduced) {
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          revealObserver.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    });
    
    revealSections.forEach(section => revealObserver.observe(section));
  } else {
    revealSections.forEach(section => section.classList.add('visible'));
  }
  
  // Floating character cards animation
  const floatChars = document.querySelectorAll('.float-char');
  if (floatChars.length > 0 && !reduced) {
    floatChars.forEach((char, i) => {
      const duration = 12 + i * 2;
      char.style.setProperty('--float-duration', `${duration}s`);
      char.style.setProperty('--i', i);
    });
  }
  
  // Particle system for hero
  initParticles();
  
  // Parallax for hero elements
  if (!reduced) {
    const hero = document.querySelector('.hero');
    const sky = document.querySelector('.sky');
    const hills = document.querySelector('.hills');
    const floaters = document.querySelectorAll('.floater');
    
    if (hero) {
      hero.addEventListener('mousemove', (e) => {
        const rect = hero.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        
        if (sky) {
          gsap.to(sky, { x: x * 30, y: y * 20, duration: 1, ease: 'power2.out' });
        }
        if (hills) {
          gsap.to(hills, { x: x * 15, y: y * 10, duration: 1, ease: 'power2.out' });
        }
        floaters.forEach((floater, i) => {
          const depth = 20 + (i % 3) * 10;
          gsap.to(floater, { x: x * depth, y: y * depth * 0.7, duration: 1.2, ease: 'power2.out' });
        });
      });
    }
  }
  
  // Character card hover effects
  const charCards = document.querySelectorAll('.character-card');
  charCards.forEach(card => {
    card.addEventListener('mouseenter', () => {
      if (!reduced) {
        const img = card.querySelector('img');
        if (img) {
          gsap.to(img, { scale: 1.1, duration: 0.5, ease: 'power2.out' });
        }
      }
    });
    
    card.addEventListener('mouseleave', () => {
      if (!reduced) {
        const img = card.querySelector('img');
        if (img) {
          gsap.to(img, { scale: 1, duration: 0.5, ease: 'power2.out' });
        }
      }
    });
  });
  
  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
}

/* ---------- Particle System ---------- */
function initParticles() {
  const particlesContainer = document.getElementById('particles');
  if (!particlesContainer) return;
  
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduced) return;
  
  const particleCount = 30;
  const colors = ['#FF6B35', '#FFD567', '#FF8C5A', '#FFFDF7', '#FFB6C1'];
  
  for (let i = 0; i < particleCount; i++) {
    const particle = document.createElement('i');
    const size = Math.random() * 6 + 2;
    const color = colors[Math.floor(Math.random() * colors.length)];
    const startX = Math.random() * window.innerWidth;
    const startY = Math.random() * window.innerHeight;
    const duration = Math.random() * 20 + 15;
    const delay = Math.random() * 5;
    
    particle.style.cssText = `
      position: absolute;
      left: ${startX}px;
      top: ${startY}px;
      width: ${size}px;
      height: ${size}px;
      border-radius: 50%;
      background: ${color};
      opacity: ${Math.random() * 0.3 + 0.1};
      pointer-events: none;
      animation: particleFloat ${duration}s ease-in-out ${delay}s infinite;
    `;
    
    particlesContainer.appendChild(particle);
  }
  
  // Add particle animation keyframes dynamically
  if (!document.getElementById('particle-styles')) {
    const style = document.createElement('style');
    style.id = 'particle-styles';
    style.textContent = `
      @keyframes particleFloat {
        0%, 100% {
          transform: translate3d(0, 0, 0) scale(1);
          opacity: 0.1;
        }
        25% {
          transform: translate3d(100px, -150px, 0) scale(1.2);
          opacity: 0.3;
        }
        50% {
          transform: translate3d(-80px, -300px, 0) scale(0.8);
          opacity: 0.2;
        }
        75% {
          transform: translate3d(120px, -200px, 0) scale(1.1);
          opacity: 0.25;
        }
      }
    `;
    document.head.appendChild(style);
  }
}

/* ---------- Mobile Menu (runs after DOM ready) ---------- */
(function() {
  const $ = s => document.querySelector(s);
  const $$ = s => [...document.querySelectorAll(s)];
  
  const mobileMenuToggle = document.getElementById('mobileMenuToggle');
  const mainNav = document.getElementById('mainNav');
  
  console.log('Mobile menu script running', { toggle: !!mobileMenuToggle, nav: !!mainNav });
  
  if (!mobileMenuToggle || !mainNav) {
    console.warn('Mobile menu elements not found');
    return;
  }
  
  console.log('Mobile menu elements found, initializing');
  
  mobileMenuToggle.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    console.log('Hamburger clicked');
    const isExpanded = this.getAttribute('aria-expanded') === 'true';
    this.setAttribute('aria-expanded', !isExpanded);
    mainNav.classList.toggle('open');
    console.log('Menu toggled, open:', mainNav.classList.contains('open'));
    
    if (!isExpanded) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  });
  
  const closeMenu = () => {
    mobileMenuToggle.setAttribute('aria-expanded', 'false');
    mainNav.classList.remove('open');
    document.body.style.overflow = '';
  };
  
  document.querySelectorAll('#mainNav .nav-btn').forEach(link => {
    link.addEventListener('click', closeMenu);
  });
  
  document.addEventListener('click', (e) => {
    if (!mainNav.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
      closeMenu();
    }
  });
  
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mainNav.classList.contains('open')) {
      closeMenu();
    }
  });
  
  console.log('Mobile menu initialized successfully');
})();