(() => {
  'use strict';
  const $ = s => document.querySelector(s);
  const $$ = s => [...document.querySelectorAll(s)];
  const vw = () => window.innerWidth, vh = () => window.innerHeight;
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  let hasIntro = false;
  
  function checkHasIntro() {
    hasIntro = !!document.getElementById('intro');
    console.log('hasIntro:', hasIntro, document.getElementById('intro'));
    return hasIntro;
  }

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
      STAR = '<svg viewBox="0 0 100 100"><path d="M50 21 L59.5 44 L84.5 46 L65.5 62 L71.5 86 L50 73 L28.5 86 L34.5 62 L15.5 46 L40.5 44 Z" fill="#FFFDF7" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/><circle cx="43" cy="52" r="3.2" fill="#33261D"/><circle cx="57" cy="52" r="3.2" fill="#33261D"/><path d="M46 59 Q50 62.5 54 59" stroke="#33261D" stroke-width="2.6" fill="none" stroke-linecap="round"/></svg>';
      STAR_GOLD = '<svg viewBox="0 0 100 100"><path d="M50 21 L59.5 44 L84.5 46 L65.5 62 L71.5 86 L50 73 L28.5 86 L34.5 62 L15.5 46 L40.5 44 Z" fill="#FFC531" stroke="#33261D" stroke-width="5" stroke-linejoin="round"/><circle cx="43" cy="52" r="3.2" fill="#33261D"/><circle cx="57" cy="52" r="3.2" fill="#33261D"/><path d="M46 59 Q50 62.5 54 59" stroke="#33261D" stroke-width="2.6" fill="none" stroke-linecap="round"/></svg>';
    }
  }
  const sparkSVG = c => `<svg viewBox="0 0 20 20"><path d="M10 1L12 8 19 10 12 12 10 19 8 12 1 10 8 8Z" fill="${c}"/></svg>`;

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

  let paraList = [];
  function makePara(entries) {
    paraList = entries.filter(e => e.el).map(e => ({ 
      el: e.el, 
      depth: 8 + (e.index % 3) * 7 
    }));
  }
  // Expose globally for intro.js
  window.makePara = makePara;
  window.paraList = paraList;
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) { 
    makePara([]); 
  }
  window.addEventListener('pointermove', e => {
    const nx = e.clientX / vw() - .5, ny = e.clientY / vh() - .5;
    paraList.forEach(p => {
      if (p.el) {
        gsap.to(p.el, { x: nx * p.depth, y: ny * p.depth * 0.7, duration: 0.5, ease: 'power2.out' });
      }
    });
  });
  const sitePara = [
    { el: $('.sky'), depth: 12 }, { el: $('.hills'), depth: 6 }, { el: $('.hero'), depth: 8 },
    { el: $('.f-fox'), depth: 26 }, { el: $('.f-bunny'), depth: 20 },
    { el: $('.f-star'), depth: 16 }, { el: $('.f-balloon'), depth: 30 }
  ];

  function ambient() {
    makePara(sitePara);
  }

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
    initHomePage();
    
    checkHasIntro();
    if (hasIntro && window.IntroAnimation) {
      window.introAnimation = new window.IntroAnimation();
      window.introAnimation.init();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      checkHasIntro();
      if (hasIntro) {
        if (document.fonts && document.fonts.ready) {
          document.fonts.ready.then(init);
        } else {
          init();
        }
      } else {
        init();
      }
    });
  } else {
    checkHasIntro();
    if (hasIntro) {
      if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(init);
      } else {
        init();
      }
    } else {
      init();
    }
  }
})();

function pageEntrance() {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  gsap.set('#site', { scale: 1, autoAlpha: 1 });
  gsap.fromTo('.site-head', { autoAlpha: 0, y: -18 }, { autoAlpha: 1, y: 0, duration: .6, ease: 'power2.out' });
  gsap.fromTo('.hero', { autoAlpha: 0, y: 30 }, { autoAlpha: 1, y: 0, duration: 1, ease: 'power3.out', delay: .2 });
  if (!reduced) {
    gsap.fromTo('.floater', { autoAlpha: 0, y: 46, scale: .7 },
      { autoAlpha: 1, y: 0, scale: 1, duration: .8, ease: 'back.out(1.8)', stagger: 0.09 }, 0.3);
  }
}

function initHomePage() {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  
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
  
  const floatChars = document.querySelectorAll('.float-char');
  if (floatChars.length > 0 && !reduced) {
    floatChars.forEach((char, i) => {
      const duration = 12 + i * 2;
      char.style.setProperty('--float-duration', `${duration}s`);
      char.style.setProperty('--i', i);
    });
  }
  
  initParticles();
  
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

/* ================================================================
   Desktop Categories Sidebar — independent controller
   ================================================================ */
(function() {
  const $ = s => document.querySelector(s);
  const $$ = s => [...document.querySelectorAll(s)];
  
  const desktopMenuToggle = document.getElementById('desktopMenuToggle');
  const desktopSidebar = document.getElementById('desktopCategorySidebar');
  const desktopSidebarClose = document.getElementById('desktopSidebarClose');
  const desktopSidebarOverlay = document.getElementById('desktopSidebarOverlay');
  const mobileDrawer = document.getElementById('mobileNavigationDrawer');
  const mobileDrawerToggle = document.getElementById('mobileMenuToggle');
  
  if (!desktopMenuToggle || !desktopSidebar) {
    return;
  }
  
  const scrollLockTargets = () => document.querySelectorAll('.page-wrap, #mainContent');
  
  const lockScroll = () => {
    scrollLockTargets().forEach(el => { el.style.overflow = 'hidden'; });
  };
  
  const unlockScroll = () => {
    scrollLockTargets().forEach(el => { el.style.overflow = ''; });
  };
  
  const closeMobileDrawer = () => {
    if (mobileDrawer && mobileDrawer.classList.contains('open')) {
      mobileDrawer.classList.remove('open');
      if (mobileDrawerToggle) mobileDrawerToggle.setAttribute('aria-expanded', 'false');
      const mobileOverlay = document.getElementById('mobileDrawerOverlay');
      if (mobileOverlay) mobileOverlay.classList.remove('visible');
      unlockScroll();
    }
  };
  
  const openDesktopSidebar = () => {
    closeMobileDrawer();
    desktopSidebar.hidden = false;
    desktopSidebar.setAttribute('aria-hidden', 'false');
    // Force reflow
    desktopSidebar.offsetHeight;
    desktopSidebar.classList.add('open');
    desktopMenuToggle.setAttribute('aria-expanded', 'true');
    if (desktopSidebarOverlay) {
      desktopSidebarOverlay.classList.add('visible');
      desktopSidebarOverlay.setAttribute('aria-hidden', 'false');
    }
    lockScroll();
    desktopSidebarClose?.focus();
  };
  
  const closeDesktopSidebar = () => {
    desktopSidebar.classList.remove('open');
    desktopMenuToggle.setAttribute('aria-expanded', 'false');
    if (desktopSidebarOverlay) {
      desktopSidebarOverlay.classList.remove('visible');
      desktopSidebarOverlay.setAttribute('aria-hidden', 'true');
    }
    // Wait for transition before hiding
    setTimeout(() => {
      if (desktopMenuToggle.getAttribute('aria-expanded') === 'false') {
        desktopSidebar.hidden = true;
        desktopSidebar.setAttribute('aria-hidden', 'true');
      }
    }, 350);
    unlockScroll();
  };
  
  desktopMenuToggle.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    const isExpanded = desktopMenuToggle.getAttribute('aria-expanded') === 'true';
    if (isExpanded) {
      closeDesktopSidebar();
    } else {
      openDesktopSidebar();
    }
  });
  
  desktopSidebarClose?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    closeDesktopSidebar();
  });
  
  desktopSidebarOverlay?.addEventListener('click', closeDesktopSidebar);
  
  // Close on nav link click
  desktopSidebar.querySelectorAll('.sidebar-nav-btn').forEach(link => {
    link.addEventListener('click', closeDesktopSidebar);
  });
  
  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !desktopSidebar.hidden && desktopSidebar.classList.contains('open')) {
      closeDesktopSidebar();
    }
  });
  
  console.log('Desktop categories sidebar initialized');
})();

/* ================================================================
   Mobile Navigation Drawer — independent controller
   ================================================================ */
(function() {
  const $ = s => document.querySelector(s);
  
  const mobileMenuToggle = document.getElementById('mobileMenuToggle');
  const mobileDrawer = document.getElementById('mobileNavigationDrawer');
  const mobileDrawerClose = document.getElementById('mobileDrawerClose');
  const mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');
  const desktopSidebar = document.getElementById('desktopCategorySidebar');
  const desktopMenuToggle = document.getElementById('desktopMenuToggle');
  
  if (!mobileMenuToggle || !mobileDrawer) {
    console.warn('Mobile drawer elements not found');
    return;
  }
  
  const scrollLockTargets = () => document.querySelectorAll('.page-wrap, #mainContent');
  
  const lockScroll = () => {
    scrollLockTargets().forEach(el => { el.style.overflow = 'hidden'; });
  };
  
  const unlockScroll = () => {
    scrollLockTargets().forEach(el => { el.style.overflow = ''; });
  };
  
  const closeDesktopSidebar = () => {
    if (desktopSidebar && !desktopSidebar.hidden && desktopSidebar.classList.contains('open')) {
      desktopSidebar.classList.remove('open');
      if (desktopMenuToggle) desktopMenuToggle.setAttribute('aria-expanded', 'false');
      const desktopOverlay = document.getElementById('desktopSidebarOverlay');
      if (desktopOverlay) desktopOverlay.classList.remove('visible');
      setTimeout(() => {
        if (desktopMenuToggle && desktopMenuToggle.getAttribute('aria-expanded') === 'false') {
          desktopSidebar.hidden = true;
        }
      }, 350);
      unlockScroll();
    }
  };
  
  const openMobileDrawer = () => {
    closeDesktopSidebar();
    mobileDrawer.hidden = false;
    mobileDrawer.setAttribute('aria-hidden', 'false');
    mobileDrawer.offsetHeight;
    mobileDrawer.classList.add('open');
    mobileMenuToggle.setAttribute('aria-expanded', 'true');
    if (mobileDrawerOverlay) {
      mobileDrawerOverlay.classList.add('visible');
      mobileDrawerOverlay.setAttribute('aria-hidden', 'false');
    }
    lockScroll();
    mobileDrawerClose?.focus();
  };
  
  const closeMobileDrawer = () => {
    mobileDrawer.classList.remove('open');
    mobileMenuToggle.setAttribute('aria-expanded', 'false');
    if (mobileDrawerOverlay) {
      mobileDrawerOverlay.classList.remove('visible');
      mobileDrawerOverlay.setAttribute('aria-hidden', 'true');
    }
    setTimeout(() => {
      if (mobileMenuToggle.getAttribute('aria-expanded') === 'false') {
        mobileDrawer.hidden = true;
        mobileDrawer.setAttribute('aria-hidden', 'true');
      }
    }, 350);
    unlockScroll();
  };
  
  mobileMenuToggle.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    const isExpanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';
    if (isExpanded) {
      closeMobileDrawer();
    } else {
      openMobileDrawer();
    }
  });
  
  mobileDrawerClose?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    closeMobileDrawer();
  });
  
  mobileDrawerOverlay?.addEventListener('click', closeMobileDrawer);
  
  // Close on nav link click
  mobileDrawer.querySelectorAll('.nav-btn').forEach(link => {
    link.addEventListener('click', closeMobileDrawer);
  });
  
  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mobileDrawer.classList.contains('open')) {
      closeMobileDrawer();
    }
  });
  
  console.log('Mobile navigation drawer initialized');
})();

/* ================================================================
   Breakpoint handling — close menus when resizing across breakpoints
   ================================================================ */
(function() {
  const $ = s => document.querySelector(s);
  
  let mql = matchMedia('(min-width: 1025px)');
  
  const restoreScroll = () => {
    document.querySelectorAll('.page-wrap, #mainContent').forEach(el => { el.style.overflow = ''; });
  };
  
  const handleBreakpointChange = (e) => {
    const desktopSidebar = $('#desktopCategorySidebar');
    const mobileDrawer = $('#mobileNavigationDrawer');
    const desktopOverlay = $('#desktopSidebarOverlay');
    const mobileOverlay = $('#mobileDrawerOverlay');
    const desktopToggle = $('#desktopMenuToggle');
    const mobileToggle = $('#mobileMenuToggle');
    
    if (e.matches) {
      // Crossing to desktop — close mobile drawer if open
      if (mobileDrawer && mobileDrawer.classList.contains('open')) {
        mobileDrawer.classList.remove('open');
        mobileDrawer.setAttribute('aria-hidden', 'true');
        if (mobileToggle) mobileToggle.setAttribute('aria-expanded', 'false');
        if (mobileOverlay) {
          mobileOverlay.classList.remove('visible');
          mobileOverlay.setAttribute('aria-hidden', 'true');
        }
        restoreScroll();
      }
    } else {
      // Crossing to mobile — close desktop sidebar if open
      if (desktopSidebar && !desktopSidebar.hidden && desktopSidebar.classList.contains('open')) {
        desktopSidebar.classList.remove('open');
        desktopSidebar.setAttribute('aria-hidden', 'true');
        if (desktopToggle) desktopToggle.setAttribute('aria-expanded', 'false');
        if (desktopOverlay) {
          desktopOverlay.classList.remove('visible');
          desktopOverlay.setAttribute('aria-hidden', 'true');
        }
        setTimeout(() => {
          if (desktopToggle && desktopToggle.getAttribute('aria-expanded') === 'false') {
            desktopSidebar.hidden = true;
          }
        }, 350);
        restoreScroll();
      }
    }
  };
  
  mql.addEventListener('change', handleBreakpointChange);
  
  console.log('Breakpoint handler initialized');
})();

/* ================================================================
   Featured Slider Controller
   ================================================================ */
(function() {
  const $ = s => document.querySelector(s);
  const $$ = s => [...document.querySelectorAll(s)];
  
  const featuredSlider = $('.featured-slider');
  if (!featuredSlider) return;
  
  const track = featuredSlider.querySelector('.slider-track');
  const prevBtn = featuredSlider.querySelector('.slider-prev');
  const nextBtn = featuredSlider.querySelector('.slider-next');
  const dotsContainer = featuredSlider.querySelector('.slider-dots');
  const slides = featuredSlider.querySelectorAll('.slider-slide');
  const dots = dotsContainer ? dotsContainer.querySelectorAll('.slider-dot') : [];
  
  if (slides.length === 0) return;
  
  let currentIndex = 0;
  let autoRotateTimer = null;
  const AUTO_ROTATE_INTERVAL = 5000;
  let isAnimating = false;
  
  function getSlideWidth() {
    const slide = slides[0];
    if (!slide) return 300;
    const style = window.getComputedStyle(slide);
    return slide.offsetWidth + parseInt(style.marginRight || '0') + parseInt(style.marginLeft || '0');
  }
  
  function getVisibleCount() {
    const containerWidth = featuredSlider.clientWidth - (prevBtn ? prevBtn.offsetWidth : 0) - (nextBtn ? nextBtn.offsetWidth : 0);
    const slideWidth = getSlideWidth();
    return Math.max(1, Math.floor(containerWidth / slideWidth));
  }
  
  function updateSlider() {
    if (!track) return;
    
    const visibleCount = getVisibleCount();
    const maxIndex = Math.max(0, slides.length - visibleCount);
    
    if (currentIndex > maxIndex) currentIndex = maxIndex;
    if (currentIndex < 0) currentIndex = 0;
    
    const slideWidth = getSlideWidth();
    const offset = currentIndex * slideWidth;
    track.style.transform = `translateX(-${offset}px)`;
    
    dots.forEach((dot, i) => {
      const isActive = i === currentIndex;
      dot.classList.toggle('active', isActive);
      dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
    
    if (prevBtn) prevBtn.disabled = currentIndex <= 0;
    if (nextBtn) nextBtn.disabled = currentIndex >= maxIndex;
  }
  
  function goToSlide(index) {
    if (isAnimating) return;
    currentIndex = index;
    updateSlider();
    startAutoRotate();
  }
  
  function nextSlide() {
    const visibleCount = getVisibleCount();
    const maxIndex = Math.max(0, slides.length - visibleCount);
    if (currentIndex < maxIndex) {
      goToSlide(currentIndex + 1);
    } else {
      goToSlide(0);
    }
  }
  
  function prevSlide() {
    if (currentIndex > 0) {
      goToSlide(currentIndex - 1);
    } else {
      const visibleCount = getVisibleCount();
      const maxIndex = Math.max(0, slides.length - visibleCount);
      goToSlide(maxIndex);
    }
  }
  
  function startAutoRotate() {
    stopAutoRotate();
    autoRotateTimer = setInterval(nextSlide, AUTO_ROTATE_INTERVAL);
  }
  
  function stopAutoRotate() {
    if (autoRotateTimer) {
      clearInterval(autoRotateTimer);
      autoRotateTimer = null;
    }
  }
  
  if (prevBtn) {
    prevBtn.addEventListener('click', (e) => {
      e.preventDefault();
      stopAutoRotate();
      prevSlide();
      startAutoRotate();
    });
  }
  
  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.preventDefault();
      stopAutoRotate();
      nextSlide();
      startAutoRotate();
    });
  }
  
  dots.forEach((dot, index) => {
    dot.addEventListener('click', (e) => {
      e.preventDefault();
      stopAutoRotate();
      goToSlide(index);
      startAutoRotate();
    });
  });
  
  featuredSlider.addEventListener('mouseenter', stopAutoRotate);
  featuredSlider.addEventListener('mouseleave', startAutoRotate);
  
  let touchStartX = 0;
  let touchEndX = 0;
  
  track.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
    stopAutoRotate();
  }, { passive: true });
  
  track.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    const diff = touchStartX - touchEndX;
    const threshold = 50;
    if (Math.abs(diff) > threshold) {
      if (diff > 0) {
        nextSlide();
      } else {
        prevSlide();
      }
      startAutoRotate();
    }
  }, { passive: true });
  
  // Initialize
  updateSlider();
  startAutoRotate();
  
  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(updateSlider, 200);
  });
  
  console.log('Featured slider initialized');
})();
/* ============================================================
   FAVORITE & LIKE BUTTONS — API wiring
   (These buttons existed on show/movie/watch/index pages but had
   no click handlers, so clicking them did nothing.)
   ============================================================ */
(() => {
  'use strict';

  const toastEl = document.getElementById('toast');
  let toastTO;
  const toast = (msg, icon = 'star') => {
    if (!toastEl) return;
    toastEl.innerHTML = (icon === 'play'
      ? '<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>'
      : '<svg viewBox="0 0 24 24"><path d="M12 2.5l2.6 5.9 6.4.6-4.9 4.2 1.5 6.3L12 16.3 6.4 19.5l1.5-6.3L3 9l6.4-.6z" fill="currentColor"/></svg>')
      + `<span>${msg}</span>`;
    toastEl.classList.add('show');
    clearTimeout(toastTO);
    toastTO = setTimeout(() => toastEl.classList.remove('show'), 2600);
  };

  const getPageTitle = () =>
    (document.querySelector('.show-title, .movie-title, .detail-title, h1')?.textContent || '').trim();

  const requireLoginRedirect = () => {
    window.location.href = 'login.php?redirect=' + encodeURIComponent(location.pathname + location.search);
  };

  const setHeartState = (btn, active) => {
    btn.classList.toggle('active', active);
    const path = btn.querySelector('svg path');
    if (path) path.setAttribute('fill', active ? 'currentColor' : 'none');
  };

  async function toggleFavorite(btn) {
    const mediaType = btn.dataset.mediaType || 'tv';
    if (!['tv', 'movie'].includes(mediaType)) {
      toast('Only shows and movies can be favorited right now');
      return;
    }
    const tmdbId = btn.dataset.tmdbId || btn.dataset.showId || btn.dataset.movieId;
    if (!tmdbId) return;

    btn.disabled = true;
    try {
      const res = await fetch('api/favorites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'toggle',
          tmdb_id: Number(tmdbId),
          media_type: mediaType,
          title: btn.dataset.title || getPageTitle() || document.title.split('—')[0].trim(),
          poster_path: btn.dataset.poster || null
        })
      });
      if (res.status === 401) { requireLoginRedirect(); return; }
      const data = await res.json();
      if (data.success) {
        setHeartState(btn, data.favorited);
        const label = btn.querySelector('span:not(.like-count)');
        if (label && /FAVORIT/i.test(label.textContent)) {
          label.textContent = data.favorited ? 'FAVORITED' : 'ADD TO FAVORITES';
        }
        toast(data.message || (data.favorited ? 'Added to favorites' : 'Removed from favorites'));
      } else {
        toast(data.message || 'Something went wrong');
      }
    } catch (err) {
      console.error(err);
      toast('Network error. Please try again.');
    } finally {
      btn.disabled = false;
    }
  }

  async function toggleLike(btn) {
    const mediaType = btn.dataset.mediaType || 'tv';
    if (!['tv', 'movie'].includes(mediaType)) return;
    const tmdbId = btn.dataset.tmdbId || btn.dataset.showId || btn.dataset.movieId;
    if (!tmdbId) return;

    btn.disabled = true;
    try {
      const res = await fetch('api/likes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle', tmdb_id: Number(tmdbId), media_type: mediaType })
      });
      if (res.status === 401) { requireLoginRedirect(); return; }
      const data = await res.json();
      if (data.success) {
        setHeartState(btn, data.liked);
        const count = btn.querySelector('.like-count');
        if (count && typeof data.count === 'number') count.textContent = Number(data.count).toLocaleString();
      }
    } catch (err) {
      console.error(err);
      toast('Network error. Please try again.');
    } finally {
      btn.disabled = false;
    }
  }

  document.addEventListener('click', (e) => {
    const favBtn = e.target.closest('.favorite-btn');
    if (favBtn) { e.preventDefault(); toggleFavorite(favBtn); return; }
    const likeBtn = e.target.closest('.like-btn');
    if (likeBtn) { e.preventDefault(); toggleLike(likeBtn); }
  });
})();
