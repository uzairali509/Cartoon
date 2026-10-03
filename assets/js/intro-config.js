/**
 * Intro Animation Configuration
 * 
 * Replace the GIF paths below with your own cartoon GIFs.
 * The animation system will automatically create cards for each entry.
 * 
 * Structure:
 * - gif: path to the GIF file (relative to project root)
 * - phase: 'chain' for sequential cards, 'collage' for reveal phase
 * - type: 'main' for center card, 'side' for surrounding cards
 * - landscape: true for landscape orientation
 */

const INTRO_CARDS = [
    // Phase 1: Chain cards (sequential zig-zag)
    {
        gif: 'assets/gifs/fox.gif',
        phase: 'chain',
        index: 0
    },
    {
        gif: 'assets/gifs/rocket.gif',
        phase: 'chain',
        index: 1
    },
    {
        gif: 'assets/gifs/cactus.gif',
        phase: 'chain',
        index: 2
    },
    {
        gif: 'assets/gifs/bear.gif',
        phase: 'chain',
        index: 3
    },
    {
        gif: 'assets/gifs/icecream.gif',
        phase: 'chain',
        index: 4
    },
    {
        gif: 'assets/gifs/balloon.gif',
        phase: 'chain',
        index: 5
    },
    {
        gif: 'assets/gifs/burger.gif',
        phase: 'chain',
        index: 6
    },

    // Phase 2: Collage cards (revealed after white transition)
    {
        gif: 'assets/gifs/world.gif',
        phase: 'collage',
        type: 'main'
    },
    {
        gif: 'assets/gifs/rainbow.gif',
        phase: 'collage',
        type: 'side',
        landscape: true
    },
    {
        gif: 'assets/gifs/bunny.gif',
        phase: 'collage',
        type: 'side'
    },
    {
        gif: 'assets/gifs/cat.gif',
        phase: 'collage',
        type: 'side'
    },
    {
        gif: 'assets/gifs/star.gif',
        phase: 'collage',
        type: 'side'
    },
    {
        gif: 'assets/gifs/controller.gif',
        phase: 'collage',
        type: 'side'
    },
    {
        gif: 'assets/gifs/icecream.gif',
        phase: 'collage',
        type: 'side'
    },
    {
        gif: 'assets/gifs/burger.gif',
        phase: 'collage',
        type: 'side',
        landscape: true
    }
];

// Animation timing configuration
const INTRO_TIMING = {
    // Phase 1: Card chain
    chain: {
        cardDelay: 0.4,        // delay between each card
        cardDuration: 0.95,    // animation duration per card
        staggerStart: 0.3,     // initial delay before first card
        elasticDelay: 0.5      // delay before internal GIF animation
    },
    
    // Phase 2: Scatter
    scatter: {
        label: 3.9,
        stagger: 0.03,
        duration: 0.9,
        fadeDelay: 0.5,
        fadeDuration: 0.32
    },
    
    // Phase 3: White transition
    white: {
        delay: 1.22,
        panelDuration: 0.72,
        flashIn: 0.12,
        flashOut: 0.55,
        brandMarkDelay: 0.15,
        brandMarkDuration: 0.5,
        brandMarkFadeDelay: 0.62,
        brandMarkFadeDuration: 0.28
    },
    
    // Phase 4: Collage reveal
    reveal: {
        delay: 1.02,
        initialDuration: 0.5,
        settleDuration: 0.55,
        stagger: 0.11,
        mainCardDelay: 0.78,
        formedDelay: 1.5,
        floatDuration: 0.85
    },
    
    // Phase 5: Explosion
    boom: {
        delay: 0.85,
        duration: 0.95,
        stagger: 0.14,
        fadeDelay: 0.55,
        fadeStagger: 0.07,
        mainCardDuration: 0.55,
        panelDuration: 0.8,
        bgDuration: 0.8,
        flashDuration: 0.1,
        flashOut: 0.5,
        siteScaleDuration: 1.3
    },
    
    // Phase 6: Site reveal
    site: {
        delay: 0.55,
        floaterDelay: 0.3,
        floaterStagger: 0.09,
        titleDelay: 0,
        titleStagger: 0.14,
        headerDelay: 0.35,
        taglineDelay: 0.5,
        navDelay: 0.62,
        navStagger: 0.07,
        ticketDelay: 1.05
    }
};

// Card sizing configuration
const CARD_SIZES = {
    chain: {
        width: 'clamp(104px, 23vw, 250px)',
        aspectRatio: '4/5'
    },
    collage: {
        side: {
            width: 'clamp(96px, 19vw, 215px)',
            aspectRatio: '4/5'
        },
        sideLand: {
            width: 'clamp(140px, 26vw, 300px)',
            aspectRatio: '5/4'
        },
        main: {
            width: 'clamp(175px, 31vw, 340px)',
            aspectRatio: '4/5'
        }
    }
};

// Z-index layers
const Z_INDEX = {
    introBg: 0,
    chainLayer: 1,
    whitePanel: 10,
    collageLayer: 20,
    mainCard: 50,
    brandMark: 25,
    flash: 30,
    skipBtn: 60
};

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { INTRO_CARDS, INTRO_TIMING, CARD_SIZES, Z_INDEX };
}