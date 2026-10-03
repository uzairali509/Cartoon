<?php
/**
 * Intro Animation Configuration (PHP version)
 * 
 * This configuration is used by header.php to generate the intro HTML.
 * The JavaScript version in assets/js/intro-config.js is used by intro.js
 */

return [
    // Phase 1: Chain cards (sequential zig-zag)
    'chain' => [
        ['gif' => 'assets/gifs/fox.gif', 'scene' => 'fox'],
        ['gif' => 'assets/gifs/rocket.gif', 'scene' => 'rocket'],
        ['gif' => 'assets/gifs/cactus.gif', 'scene' => 'cactus'],
        ['gif' => 'assets/gifs/bear.gif', 'scene' => 'bear'],
        ['gif' => 'assets/gifs/icecream.gif', 'scene' => 'icecream'],
        ['gif' => 'assets/gifs/balloon.gif', 'scene' => 'balloon'],
        ['gif' => 'assets/gifs/burger.gif', 'scene' => 'burger'],
    ],

    // Phase 2: Collage cards (revealed after white transition)
    'collage' => [
        'main' => ['gif' => 'assets/svg/world.svg', 'scene' => 'world'],
        'sides' => [
            ['gif' => 'assets/gifs/rainbow.gif', 'scene' => 'rainbow', 'landscape' => true],
            ['gif' => 'assets/gifs/bunny.gif', 'scene' => 'bunny', 'landscape' => false],
            ['gif' => 'assets/gifs/cat.gif', 'scene' => 'cat', 'landscape' => false],
            ['gif' => 'assets/gifs/star.gif', 'scene' => 'star', 'landscape' => false],
            ['gif' => 'assets/gifs/controller.gif', 'scene' => 'controller', 'landscape' => false],
            ['gif' => 'assets/gifs/icecream.gif', 'scene' => 'icecream', 'landscape' => false],
            ['gif' => 'assets/gifs/burger.gif', 'scene' => 'burger', 'landscape' => true],
        ]
    ]
];