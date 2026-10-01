// data.js — All site content in one place.
// This replaces data.php for the static frontend.

export const SITE_DATA = {
  site: {
    name: 'Cartoon Universe',
    tagline: 'A hand-drawn world of tiny heroes, big feelings, and one very sleepy bear.',
    episode_tag: 'EP 12 · "The Great Jam Heist" — out now',
  },

  // Phase 1 of the intro: the chain of flying photo cards
  chain: ['fox', 'rocket', 'cactus', 'bear', 'icecream', 'balloon', 'burger'],

  // Phase 3: the collage — one big key-art card + cards around it
  collage: {
    main: 'world',
    sides: ['rainbow', 'bunny', 'cat', 'star', 'controller', 'icecream', 'burger'],
    land: ['rainbow', 'burger'], // landscape-oriented cards
  },

  characters: [
    {
      scene: 'fox',
      name: 'Ember',
      role: 'The Fox · Fearless leader',
      bio: 'Fearless, fluffy, and occasionally on fire — figuratively, mostly. Leads the crew on one terrible, wonderful adventure at a time.',
      stats: [['Courage', 92], ['Mischief', 70], ['Naps', 28]]
    },
    {
      scene: 'bunny',
      name: 'Pip',
      role: 'The Bunny · Speed demon',
      bio: 'Fastest ears in Meadowbrook. Talks twice as fast as she hops.',
      stats: []
    },
    {
      scene: 'bear',
      name: 'Bruno',
      role: 'The Bear · Professional napper',
      bio: 'Can sleep through a volcano. Has, in fact, done so.',
      stats: []
    },
    {
      scene: 'cat',
      name: 'Miso',
      role: 'The Cat · Chaotic inventor',
      bio: 'Builds machines that shouldn\'t work. They usually do. Briefly.',
      stats: []
    },
    {
      scene: 'star',
      name: 'Twinkle',
      role: 'The Star · Tiny & loud',
      bio: 'Fell out of the sky last spring. Stayed for the snacks.',
      stats: []
    },
  ],

  shows: [
    {
      num: '01',
      title: 'Ember & Friends',
      seasons: 3,
      episodes: 36,
      rating: 'TV-Y',
      pitch: 'The flagship adventures of a fox who never looks before leaping — and the friends who always catch her.'
    },
    {
      num: '02',
      title: "Twinkle's Bedtime Tales",
      seasons: 2,
      episodes: 24,
      rating: 'TV-Y',
      pitch: 'Five-minute wind-down stories, narrated by the smallest (and loudest) star in the sky.'
    },
    {
      num: '03',
      title: "Miso's Workshop",
      seasons: 1,
      episodes: 10,
      rating: 'TV-Y7',
      pitch: 'A chaotic cat builds machines that shouldn\'t work. They do. Briefly.'
    },
  ],

  episodes: [
    { ep: 12, min: 11, title: 'The Great Jam Heist', new: true },
    { ep: 11, min: 9, title: 'Cloud Surfing 101', new: false },
    { ep: 10, min: 12, title: "Bruno's Big Nap", new: false },
    { ep: 9, min: 10, title: 'The Star Who Lost Her Shine', new: false },
    { ep: 8, min: 9, title: 'Miso Machine Mayhem', new: false },
  ],
};