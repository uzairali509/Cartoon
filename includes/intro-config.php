<?php
/**
 * Intro Animation Configuration
 * 
 * Configure GIF paths for each intro card here.
 * Each card can have its own GIF file from assets/gifs/
 * 
 * The animation sequence:
 * - Phase 1 (chain): Sequential cards entering from left/bottom
 * - Phase 2 (scatter): Cards scatter outward
 * - Phase 3 (white): White panel expands, brand mark shows
 * - Phase 4 (collage): Cards reveal around center
 * - Phase 5 (boom): Cards explode outward
 * - Phase 6 (site): Homepage reveals
 */

return [
    // Phase 1: Chain cards (7 cards - alternating left/bottom entry)
    // Configure each card's GIF path here
    'chain' => [
        [
            'gif' => 'assets/gifs/benten.gif',
            'alt' => 'Benten',
            'rotation' => -7,
            'enterFrom' => 'left'
        ],
        [
            'gif' => 'assets/gifs/kunfupanda.gif',
            'alt' => 'Kung Fu Panda',
            'rotation' => 6,
            'enterFrom' => 'bottom'
        ],
        [
            'gif' => 'assets/gifs/MrBean.gif',
            'alt' => 'Mr Bean',
            'rotation' => -5,
            'enterFrom' => 'left'
        ],
        [
            'gif' => 'assets/gifs/Puppythesellerman.gif',
            'alt' => 'Puppy the Salesman',
            'rotation' => 7,
            'enterFrom' => 'bottom'
        ],
        [
            'gif' => 'assets/gifs/Tom&jerry.gif',
            'alt' => 'Tom & Jerry',
            'rotation' => -6,
            'enterFrom' => 'left'
        ],
        [
            'gif' => 'assets/gifs/Frozen.gif',
            'alt' => 'Frozen',
            'rotation' => 5,
            'enterFrom' => 'bottom'
        ],
        [
            'gif' => 'assets/gifs/Henry.gif',
            'alt' => 'Henry',
            'rotation' => -4,
            'enterFrom' => 'left'
        ],
    ],

    // Phase 3/4: Collage cards (1 main + 7 side cards)
    // Main card is the large center card
    // Side cards surround the main card
    'collage' => [
        'main' => [
            'gif' => 'assets/gifs/Spiderman.gif',
            'alt' => 'Spider-Man',
            'landscape' => false
        ],
        'sides' => [
            [
                'gif' => 'assets/gifs/DORAEMON.gif',
                'alt' => 'Doraemon',
                'landscape' => true,
                'position' => 'top'
            ],
            [
                'gif' => 'assets/gifs/picachu.gif',
                'alt' => 'Pikachu',
                'landscape' => false,
                'position' => 'left'
            ],
            [
                'gif' => 'assets/gifs/scoobydoo.gif',
                'alt' => 'Scooby Doo',
                'landscape' => false,
                'position' => 'right'
            ],
            [
                'gif' => 'assets/gifs/spongebob.gif',
                'alt' => 'SpongeBob',
                'landscape' => false,
                'position' => 'bottom-left'
            ],
            [
                'gif' => 'assets/gifs/powerpuffgirls.gif',
                'alt' => 'Powerpuff Girls',
                'landscape' => false,
                'position' => 'bottom-right'
            ],
            [
                'gif' => 'assets/gifs/bugsbunny.gif',
                'alt' => 'Bugs Bunny',
                'landscape' => false,
                'position' => 'bottom'
            ],
            [
                'gif' => 'assets/gifs/generax.gif',
                'alt' => 'Generax',
                'landscape' => true,
                'position' => 'bottom'
            ],
        ]
    ],

    // Animation timing (in seconds)
    'timing' => [
        'chain' => [
            'cardDelay' => 0.4,
            'cardDuration' => 0.95,
            'staggerStart' => 0.3,
            'elasticDelay' => 0.5
        ],
        'scatter' => [
            'label' => 3.9,
            'stagger' => 0.03,
            'duration' => 0.9,
            'fadeDelay' => 0.5,
            'fadeDuration' => 0.32
        ],
        'white' => [
            'delay' => 1.22,
            'panelDuration' => 0.72,
            'flashIn' => 0.12,
            'flashOut' => 0.55,
            'brandMarkDelay' => 0.15,
            'brandMarkDuration' => 0.5,
            'brandMarkFadeDelay' => 0.62,
            'brandMarkFadeDuration' => 0.28
        ],
        'reveal' => [
            'delay' => 1.02,
            'initialDuration' => 0.5,
            'settleDuration' => 0.55,
            'stagger' => 0.11,
            'mainCardDelay' => 0.78,
            'formedDelay' => 1.5,
            'floatDuration' => 0.85
        ],
        'boom' => [
            'delay' => 0.85,
            'duration' => 0.95,
            'stagger' => 0.14,
            'fadeDelay' => 0.55,
            'fadeStagger' => 0.07,
            'mainCardDuration' => 0.55,
            'panelDuration' => 0.8,
            'bgDuration' => 0.8,
            'flashDuration' => 0.1,
            'flashOut' => 0.5,
            'siteScaleDuration' => 1.3
        ],
        'site' => [
            'delay' => 0.55,
            'floaterDelay' => 0.3,
            'floaterStagger' => 0.09,
            'titleDelay' => 0,
            'titleStagger' => 0.14,
            'headerDelay' => 0.35,
            'taglineDelay' => 0.5,
            'navDelay' => 0.62,
            'navStagger' => 0.07,
            'ticketDelay' => 1.05
        ]
    ],

    // Card sizing (responsive)
    'cardSizes' => [
        'chain' => [
            'width' => 'clamp(104px, 23vw, 250px)',
            'aspectRatio' => '4/5'
        ],
        'collage' => [
            'side' => [
                'width' => 'clamp(96px, 19vw, 215px)',
                'aspectRatio' => '4/5'
            ],
            'sideLand' => [
                'width' => 'clamp(140px, 26vw, 300px)',
                'aspectRatio' => '5/4'
            ],
            'main' => [
                'width' => 'clamp(175px, 31vw, 340px)',
                'aspectRatio' => '4/5'
            ]
        ]
    ],

    // Z-index layers
    'zIndex' => [
        'introBg' => 0,
        'chainLayer' => 1,
        'whitePanel' => 10,
        'collageLayer' => 20,
        'mainCard' => 50,
        'brandMark' => 25,
        'flash' => 30,
        'skipBtn' => 60
    ]
];