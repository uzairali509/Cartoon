<?php
/**
 * data.php — All site content in one place.
 * 
 * This replaces the static JS data file for the PHP frontend.
 * Later, this data can be replaced with database queries.
 * 
 * TODO: Replace static arrays with database queries when implementing the database layer.
 * Example:
 * $characters = $pdo->query("SELECT * FROM characters")->fetchAll(PDO::FETCH_ASSOC);
 */

return [
    'site' => [
        'name'        => 'Cartoon Universe',
        'tagline'     => 'A hand-drawn world of tiny heroes, big feelings, and one very sleepy bear.',
        'episode_tag' => 'EP 12 · "The Great Jam Heist" — out now',
    ],

    // Phase 1 of the intro: the chain of flying photo cards
    'chain' => ['fox', 'rocket', 'cactus', 'bear', 'icecream', 'balloon', 'burger'],

    // Phase 3: the collage — one big key-art card + cards around it
    'collage' => [
        'main'  => 'rainbow',
        'sides' => ['rainbow', 'bunny', 'cat', 'star', 'controller', 'icecream', 'burger'],
        'land'  => ['rainbow', 'burger'], // landscape-oriented cards
    ],

    'characters' => [
        [
            'scene' => 'fox',
            'name' => 'Ember',
            'role' => 'The Fox · Fearless leader',
            'bio' => 'Fearless, fluffy, and occasionally on fire — figuratively, mostly. Leads the crew on one terrible, wonderful adventure at a time.',
            'stats' => [['Courage', 92], ['Mischief', 70], ['Naps', 28]]
        ],
        [
            'scene' => 'bunny',
            'name' => 'Pip',
            'role' => 'The Bunny · Speed demon',
            'bio' => 'Fastest ears in Meadowbrook. Talks twice as fast as she hops.',
            'stats' => []
        ],
        [
            'scene' => 'bear',
            'name' => 'Bruno',
            'role' => 'The Bear · Professional napper',
            'bio' => 'Can sleep through a volcano. Has, in fact, done so.',
            'stats' => []
        ],
        [
            'scene' => 'cat',
            'name' => 'Miso',
            'role' => 'The Cat · Chaotic inventor',
            'bio' => "Builds machines that shouldn't work. They usually do. Briefly.",
            'stats' => []
        ],
        [
            'scene' => 'star',
            'name' => 'Twinkle',
            'role' => 'The Star · Tiny & loud',
            'bio' => 'Fell out of the sky last spring. Stayed for the snacks.',
            'stats' => []
        ],
    ],

    'shows' => [
        [
            'num' => '01',
            'title' => 'Ember & Friends',
            'seasons' => 3,
            'episodes' => 36,
            'rating' => 'TV-Y',
            'pitch' => 'The flagship adventures of a fox who never looks before leaping — and the friends who always catch her.'
        ],
        [
            'num' => '02',
            'title' => "Twinkle's Bedtime Tales",
            'seasons' => 2,
            'episodes' => 24,
            'rating' => 'TV-Y',
            'pitch' => 'Five-minute wind-down stories, narrated by the smallest (and loudest) star in the sky.'
        ],
        [
            'num' => '03',
            'title' => "Miso's Workshop",
            'seasons' => 1,
            'episodes' => 10,
            'rating' => 'TV-Y7',
            'pitch' => "A chaotic cat builds machines that shouldn't work. They do. Briefly."
        ],
    ],

    'episodes' => [
        ['ep' => 12, 'min' => 11, 'title' => 'The Great Jam Heist', 'new' => true],
        ['ep' => 11, 'min' => 9, 'title' => 'Cloud Surfing 101', 'new' => false],
        ['ep' => 10, 'min' => 12, 'title' => "Bruno's Big Nap", 'new' => false],
        ['ep' => 9, 'min' => 10, 'title' => 'The Star Who Lost Her Shine', 'new' => false],
        ['ep' => 8, 'min' => 9, 'title' => 'Miso Machine Mayhem', 'new' => false],
    ],

    // SVG fallback illustrations for intro cards (scene key -> inline SVG)
    'scenes' => [
        'fox' => '<rect width="200" height="250" fill="#FF9C63"/><ellipse cx="100" cy="238" rx="96" ry="36" fill="#FFC38C" opacity=".55"/><path d="M46 86 L58 22 L94 68 Z" fill="#FF9C63" stroke="#33261D" stroke-width="5"/><path d="M154 86 L142 22 L106 68 Z" fill="#FF9C63" stroke="#33261D" stroke-width="5"/><ellipse cx="100" cy="134" rx="64" ry="57" fill="#FF9C63" stroke="#33261D" stroke-width="5"/><ellipse cx="100" cy="158" rx="33" ry="22" fill="#FFFDF7"/><circle cx="87" cy="128" r="5.5" fill="#33261D"/><circle cx="113" cy="128" r="5.5" fill="#33261D"/><path d="M93 149 L107 149 L100 159 Z" fill="#33261D"/>',
        'rocket' => '<rect width="200" height="250" fill="#2A2A2F"/><path d="M100 20 L140 240 L60 240 Z" fill="#E85D2A"/><rect x="90" y="20" width="20" height="40" fill="#F5C042"/><path d="M95 130 L105 120 L115 130 Z" fill="#FFF"/><circle cx="75" cy="220" r="12" fill="#FFFDF7" opacity=".3"/><circle cx="125" cy="220" r="12" fill="#FFFDF7" opacity=".3"/>',
        'cactus' => '<rect width="200" height="250" fill="#12A594"/><path d="M90 40 L110 200 L70 200 Z" fill="#4CAF74"/><path d="M80 120 L100 40 L120 120 Z" fill="#4CAF74"/><path d="M85 150 L115 200 L55 200 Z" fill="#4CAF74"/><ellipse cx="100" cy="190" rx="15" ry="8" fill="#2F8A57"/>',
        'bear' => '<rect width="200" height="250" fill="#3A2A2A"/><ellipse cx="100" cy="140" rx="50" ry="55" fill="#5A4A4A"/><circle cx="75" cy="100" r="18" fill="#5A4A4A"/><circle cx="125" cy="100" r="18" fill="#5A4A4A"/><ellipse cx="100" cy="165" rx="20" ry="12" fill="#3A2A2A"/><circle cx="85" cy="130" r="4" fill="#33261D"/><circle cx="115" cy="130" r="4" fill="#33261D"/>',
        'icecream' => '<rect width="200" height="250" fill="#FBE3BD"/><path d="M70 60 L130 60 L100 140 Z" fill="#FFC531"/><ellipse cx="100" cy="50" rx="25" ry="20" fill="#FF9C63"/><ellipse cx="100" cy="40" rx="22" ry="18" fill="#FFFDF7"/><path d="M90 200 L110 200 L100 230 Z" fill="#F97316"/>',
        'balloon' => '<rect width="200" height="250" fill="#E85D2A"/><path d="M80 30 Q100 10 120 30 Q100 50 80 30" fill="#FF6B35" stroke="#33261D" stroke-width="4"/><ellipse cx="100" cy="30" rx="15" ry="10" fill="#FFA07A" opacity=".6"/><path d="M100 60 Q100 65 105 70" stroke="#33261D" stroke-width="2" fill="none"/><rect x="95" y="70" width="10" height="180" fill="#33261D"/>',
        'burger' => '<rect width="200" height="250" fill="#2A2A2F"/><rect x="60" y="100" width="80" height="30" fill="#8A5A38" rx="8"/><rect x="55" y="130" width="90" height="30" fill="#FF9C63" rx="8"/><rect x="50" y="160" width="100" height="30" fill="#5A4A4A" rx="8"/><rect x="70" y="40" width="60" height="60" fill="#4CAF74"/><circle cx="85" cy="70" r="4" fill="#FFF"/><circle cx="105" cy="70" r="4" fill="#FFF"/>',
        'rainbow' => '<rect width="200" height="250" fill="#FBE3BD"/><path d="M40 196 A80 80 0 0 1 160 196" stroke="#FF5A3C" stroke-width="17" fill="none"/><path d="M58 196 A64 64 0 0 1 142 196" stroke="#FFC531" stroke-width="17" fill="none"/><path d="M76 196 A48 48 0 0 1 124 196" stroke="#12A594" stroke-width="17" fill="none"/><ellipse cx="42" cy="200" rx="27" ry="16" fill="#FFFDF7"/><ellipse cx="158" cy="200" rx="27" ry="16" fill="#FFFDF7"/>',
        'bunny' => '<rect width="200" height="250" fill="#FFFDF7"/><ellipse cx="50" cy="60" rx="28" ry="32" fill="#FFFDF7"/><ellipse cx="70" cy="60" rx="28" ry="32" fill="#FFFDF7"/><ellipse cx="30" cy="25" rx="6" ry="14" fill="#FFB6C1"/><ellipse cx="70" cy="25" rx="6" ry="14" fill="#FFB6C1"/><circle cx="42" cy="55" r="4" fill="#33261D"/><circle cx="58" cy="55" r="4" fill="#33261D"/><ellipse cx="50" cy="72" rx="8" ry="4" fill="#33261D"/>',
        'cat' => '<rect width="200" height="250" fill="#FFFDF7"/><ellipse cx="70" cy="90" rx="25" ry="20" fill="#FFFDF7"/><ellipse cx="130" cy="90" rx="25" ry="20" fill="#FFFDF7"/><circle cx="60" cy="80" r="4" fill="#33261D"/><circle cx="140" cy="80" r="4" fill="#33261D"/><path d="M100 120 Q100 130 100 140" stroke="#33261D" stroke-width="3" fill="none"/><path d="M93 149 L107 149 L100 159 Z" fill="#33261D"/>',
        'star' => '<rect width="200" height="250" fill="#FFD567"/><path d="M100 10 L116 40 L180 42 L128 62 L144 94 L100 78 L56 94 L72 62 L20 42 L84 40 Z" fill="#FFD567" stroke="#33261D" stroke-width="2"/><circle cx="84" cy="50" r="3" fill="#33261D"/><circle cx="116" cy="50" r="3" fill="#33261D"/><path d="M84 60 Q100 65 116 60" stroke="#33261D" stroke-width="2" fill="none" stroke-linecap="round"/>',
        'controller' => '<rect width="200" height="250" fill="#2A2A2F"/><rect x="70" y="90" width="60" height="70" rx="15" fill="#5A4A4A"/><rect x="85" y="105" width="10" height="10" rx="5" fill="#E85D2A"/><rect x="105" y="125" width="10" height="10" rx="5" fill="#E85D2A"/><circle cx="90" cy="145" r="8" fill="#FFFDF7"/><circle cx="110" cy="145" r="8" fill="#FFFDF7"/><rect x="80" y="165" width="40" height="10" rx="5" fill="#FFFDF7"/>',
        'burger_land' => '<rect width="250" height="200" fill="#2A2A2F"/><rect x="60" y="70" width="130" height="30" fill="#8A5A38" rx="8"/><rect x="50" y="100" width="150" height="30" fill="#FF9C63" rx="8"/><rect x="40" y="130" width="170" height="30" fill="#5A4A4A" rx="8"/><rect x="90" y="20" width="70" height="50" fill="#4CAF74"/>',
        'rainbow_land' => '<rect width="250" height="200" fill="#FBE3BD"/><path d="M50 150 A120 120 0 0 1 200 150" stroke="#FF5A3C" stroke-width="20" fill="none"/><path d="M70 150 A100 100 0 0 1 180 150" stroke="#FFC531" stroke-width="20" fill="none"/><path d="M90 150 A80 80 0 0 1 160 150" stroke="#12A594" stroke-width="20" fill="none"/>',
    ],
];