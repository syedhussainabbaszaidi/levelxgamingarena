<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Activity image search
    |--------------------------------------------------------------------------
    |
    | Admins can search for cover art while creating/editing an activity.
    | The default "library" provider searches the bundled gaming gallery below,
    | so the feature works with zero setup. Set IMAGE_SEARCH_PROVIDER=pexels
    | (and PEXELS_API_KEY) to search live photos instead — the service falls
    | back to the bundled library automatically if the API is unavailable.
    |
    */

    'provider' => env('IMAGE_SEARCH_PROVIDER', 'library'),

    'pexels_key' => env('PEXELS_API_KEY'),

    'timeout' => (int) env('IMAGE_SEARCH_TIMEOUT', 8),

    'per_page' => (int) env('IMAGE_SEARCH_PER_PAGE', 12),

    /*
    |--------------------------------------------------------------------------
    | Bundled gaming gallery
    |--------------------------------------------------------------------------
    |
    | Every entry maps an activity keyword set to a local 1200×675 photograph.
    | Files live under public/ so they load instantly with no network access.
    | Sources and licences are listed in public/img/PHOTO_CREDITS.md.
    |
    */

    'library' => [
        [
            'file' => 'img/activities/covers/ps5.jpg',
            'title' => 'PS5 Lounge',
            'keywords' => ['ps5', 'playstation', 'console', 'controller', 'gaming zone', 'tv'],
        ],
        [
            'file' => 'img/activities/covers/pc-gaming.jpg',
            'title' => 'Gaming PC',
            'keywords' => ['pc', 'computer', 'desktop', 'gaming pc', 'fps', 'valorant', 'dota'],
        ],
        [
            'file' => 'img/activities/covers/snooker.jpg',
            'title' => 'Snooker Table',
            'keywords' => ['snooker', 'billiards', 'cue', 'table'],
        ],
        [
            'file' => 'img/activities/covers/table-tennis.jpg',
            'title' => 'Table Tennis Room',
            'keywords' => ['table tennis', 'ping pong', 'tt', 'paddle', 'bat', 'net'],
        ],
        [
            'file' => 'img/activities/covers/account-packages.jpg',
            'title' => 'Account Packages',
            'keywords' => ['package', 'account', 'hours', 'prepaid', 'membership', 'bundle'],
        ],
        [
            'file' => 'img/activities/pool.svg',
            'title' => 'Pool Table',
            'keywords' => ['pool', 'billiards', '8 ball', 'eight ball', 'table'],
        ],
        [
            'file' => 'img/activities/vip-rooms.svg',
            'title' => 'VIP Room',
            'keywords' => ['vip', 'room', 'private', 'lounge', 'party'],
        ],
        [
            'file' => 'img/activities/gaming-zone.svg',
            'title' => 'Gaming Zone',
            'keywords' => ['gaming', 'zone', 'arcade', 'lounge', 'console'],
        ],
        [
            'file' => 'img/activities/default.svg',
            'title' => 'Game Activity',
            'keywords' => ['game', 'activity', 'booking', 'gaming'],
        ],
        [
            'file' => 'img/gallery/esports-arena.jpg',
            'title' => 'Esports Arena',
            'keywords' => ['esports', 'tournament', 'competition', 'arena', 'team', 'league'],
        ],
        [
            'file' => 'img/gallery/racing-sim.jpg',
            'title' => 'Racing Simulator',
            'keywords' => ['racing', 'sim', 'simulator', 'wheel', 'car', 'f1', 'driving'],
        ],
        [
            'file' => 'img/gallery/vr-zone.jpg',
            'title' => 'VR Zone',
            'keywords' => ['vr', 'virtual', 'headset', 'immersive', 'oculus'],
        ],
        [
            'file' => 'img/gallery/arcade.jpg',
            'title' => 'Arcade',
            'keywords' => ['arcade', 'retro', 'classic', 'machine', 'pinball'],
        ],
        [
            'file' => 'img/gallery/gaming-setup.jpg',
            'title' => 'Gaming Setup',
            'keywords' => ['setup', 'battlestation', 'desk', 'rgb', 'neon', 'monitor', 'stream'],
        ],
    ],

];
