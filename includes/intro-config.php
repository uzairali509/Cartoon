<?php
/**
 * Intro Animation Configuration
 *
 * Configure GIF paths for each intro card here.
 * Uses the custom artwork from assets/gifs/ (scene-based naming matching data.php).
 *
 * Edit this file to change images. No JavaScript or animation logic needs to change
 * when an image path is replaced.
 *
 * Scene keys match data.php: fox, rocket, cactus, bear, icecream, balloon, burger,
 * rainbow, bunny, cat, star, controller, world.
 *
 * Animated GIFs and static images are both supported. If a custom GIF is unavailable,
 * the cu_scene() SVG illustration is used as a fallback.
 */

return [
    // Phase 1: Chain cards (7 cards - alternating left/bottom entry)
    // Image paths reference assets/gifs/ where the custom GIFs actually live.
    'chain' => [
        [
            'scene' => 'fox',
            'gif'   => 'assets/gifs/fox.gif',
            'alt'   => 'Fox',
            'rotation' => -7,
            'enterFrom' => 'left'
        ],
        [
            'scene' => 'rocket',
            'gif'   => 'assets/gifs/rocket.gif',
            'alt'   => 'Rocket',
            'rotation' => 6,
            'enterFrom' => 'bottom'
        ],
        [
            'scene' => 'cactus',
            'gif'   => 'assets/gifs/cactus.gif',
            'alt'   => 'Cactus',
            'rotation' => -5,
            'enterFrom' => 'left'
        ],
        [
            'scene' => 'bear',
            'gif'   => 'assets/gifs/bear.gif',
            'alt'   => 'Bear',
            'rotation' => 7,
            'enterFrom' => 'bottom'
        ],
        [
            'scene' => 'icecream',
            'gif'   => 'assets/gifs/icecream.gif',
            'alt'   => 'Ice Cream',
            'rotation' => -6,
            'enterFrom' => 'left'
        ],
        [
            'scene' => 'balloon',
            'gif'   => 'assets/gifs/balloon.gif',
            'alt'   => 'Balloon',
            'rotation' => 5,
            'enterFrom' => 'bottom'
        ],
        [
            'scene' => 'burger',
            'gif'   => 'assets/gifs/burger.gif',
            'alt'   => 'Burger',
            'rotation' => -4,
            'enterFrom' => 'left'
        ],
    ],

    // Phase 3/4: Collage cards (1 main + side cards)
    'collage' => [
        'main' => [
            'scene'   => 'rainbow',
            'gif'     => 'assets/gifs/rainbow.gif',
            'alt'     => 'Rainbow',
            'landscape' => false,
        ],
        'sides' => [
            [
                'scene'   => 'rainbow',
                'gif'     => 'assets/gifs/rainbow.gif',
                'alt'     => 'Rainbow',
                'landscape' => true,
                'position'  => 'top',
            ],
            [
                'scene'   => 'bunny',
                'gif'     => 'assets/gifs/bunny.gif',
                'alt'     => 'Bunny',
                'landscape' => false,
                'position'  => 'left',
            ],
            [
                'scene'   => 'cat',
                'gif'     => 'assets/gifs/cat.gif',
                'alt'     => 'Cat',
                'landscape' => false,
                'position'  => 'right',
            ],
            [
                'scene'   => 'star',
                'gif'     => 'assets/gifs/star.gif',
                'alt'     => 'Star',
                'landscape' => false,
                'position'  => 'bottom-left',
            ],
            [
                'scene'   => 'controller',
                'gif'     => 'assets/gifs/controller.gif',
                'alt'     => 'Controller',
                'landscape' => false,
                'position'  => 'bottom-right',
            ],
            [
                'scene'   => 'icecream',
                'gif'     => 'assets/gifs/icecream.gif',
                'alt'     => 'Ice Cream',
                'landscape' => false,
                'position'  => 'bottom',
            ],
            [
                'scene'   => 'burger',
                'gif'     => 'assets/gifs/burger.gif',
                'alt'     => 'Burger',
                'landscape' => true,
                'position'  => 'bottom',
            ],
        ],
    ],

    // Animation timing (in seconds)
    'timing' => [
        'chain' => [
            'cardDelay'      => 0.4,
            'cardDuration'   => 0.95,
            'staggerStart'   => 0.3,
            'elasticDelay'   => 0.5,
        ],
        'scatter' => [
            'label'      => 3.9,
            'stagger'    => 0.03,
            'duration'   => 0.9,
            'fadeDelay'  => 0.5,
            'fadeDuration' => 0.32,
        ],
        'white' => [
            'delay'            => 1.22,
            'panelDuration'    => 0.72,
            'flashIn'          => 0.12,
            'flashOut'         => 0.55,
            'brandMarkDelay'   => 0.15,
            'brandMarkDuration'=> 0.5,
            'brandMarkFadeDelay'=> 0.62,
            'brandMarkFadeDuration'=> 0.28,
        ],
        'reveal' => [
            'delay'          => 1.02,
            'initialDuration'=> 0.5,
            'settleDuration' => 0.55,
            'stagger'        => 0.11,
            'mainCardDelay'  => 0.78,
            'formedDelay'    => 1.5,
            'floatDuration'  => 0.85,
        ],
        'boom' => [
            'delay'            => 0.85,
            'duration'         => 0.95,
            'stagger'          => 0.14,
            'fadeDelay'        => 0.55,
            'fadeStagger'      => 0.07,
            'mainCardDuration' => 0.55,
            'panelDuration'    => 0.8,
            'bgDuration'       => 0.8,
            'flashDuration'    => 0.1,
            'flashOut'         => 0.5,
            'siteScaleDuration'=> 1.3,
        ],
        'site' => [
            'delay'           => 0.55,
            'floaterDelay'    => 0.3,
            'floaterStagger'  => 0.09,
            'titleDelay'      => 0,
            'titleStagger'    => 0.14,
            'headerDelay'     => 0.35,
            'taglineDelay'    => 0.5,
            'navDelay'        => 0.62,
            'navStagger'      => 0.07,
            'ticketDelay'     => 1.05,
        ],
    ],

    // Card sizing (responsive)
    'cardSizes' => [
        'chain' => [
            'width'      => 'clamp(104px, 23vw, 250px)',
            'aspectRatio'=> '4/5',
        ],
        'collage' => [
            'side'   => [
                'width'      => 'clamp(96px, 19vw, 215px)',
                'aspectRatio'=> '4/5',
            ],
            'sideLand' => [
                'width'      => 'clamp(140px, 26vw, 300px)',
                'aspectRatio'=> '5/4',
            ],
            'main'   => [
                'width'      => 'clamp(175px, 31vw, 340px)',
                'aspectRatio'=> '4/5',
            ],
        ],
    ],

    // Z-index layers
    'zIndex' => [
        'introBg'    => 0,
        'chainLayer' => 1,
        'whitePanel' => 10,
        'collageLayer'=> 20,
        'mainCard'   => 50,
        'brandMark'  => 25,
        'flash'      => 30,
        'skipBtn'    => 60,
    ],
];
