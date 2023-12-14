<?php declare(strict_types=1);
// require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/asset_data/default_levelmap.php';
require_once __DIR__ . '/src/interaction/game_session.php';
error_reporting(E_ALL);

const WINDOW_WIDTH = 1200;
const WINDOW_HEIGHT = 768;

SDL_Init(SDL_INIT_VIDEO);
$window = SDL_CreateWindow("Jump 'n Bump", SDL_WINDOWPOS_UNDEFINED, SDL_WINDOWPOS_UNDEFINED, WINDOW_WIDTH, WINDOW_HEIGHT, SDL_WINDOW_SHOWN);
$canvas = SDL_CreateRenderer($window, -1, SDL_RENDERER_ACCELERATED | SDL_RENDERER_TARGETTEXTURE);

$current_level = create_default_level($canvas);
$current_game = new Game_Session($canvas, $current_level);
$current_game->start();


// Main loop
$event = new SDL_Event;

while (true) {
    if (SDL_PollEvent($event) && $event->type == SDL_QUIT) {
        break;
    }

    $keyboardState = SDL_GetKeyboardState($numkeys);
    $current_game->set_current_keys($keyboardState);
    $current_game->pump();
    SDL_Delay(5);
}

SDL_DestroyRenderer($canvas);
SDL_DestroyWindow($window);
SDL_Quit();
