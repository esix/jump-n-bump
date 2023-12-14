<?php
require_once __DIR__ . '/../interaction/renderer.php';
require_once __DIR__ . "/../game/objects.php";
require_once __DIR__ . "/../game/keyboard.php";
require_once __DIR__ . "/../game/ai.php";
require_once __DIR__ . "/../game/animation.php";
require_once __DIR__ . "/../resource_loading/sound_player.php";
require_once __DIR__ . "/../game/sfx.php";
require_once __DIR__ . "/../game/movement.php";
require_once __DIR__ . "/../game/game.php";
require_once __DIR__ . "/../game/player.php";
// import { Scores_ViewModel } from "../interaction/scores_viewmodel";
require_once __DIR__ . "/../env.php";

enum Game_State {
    case Not_Started;
    case Playing;
    case Paused;
}


class Game_Session {
    private $level;
    private $canvas;
    private Game_State $game_state = Game_State::Not_Started;
    private Sound_Player $sound_player;
    private Game $game;
    private Keyboard $keyboard;
    private $muted;

    public function __construct($canvas, $level) {
        $this->canvas = $canvas;
        $this->level = $level;

        $img = array(
            'rabbits' => SDL_CreateTextureFromSurface($canvas, SDL_LoadBMP(__DIR__ . '/../../assets/sprites/rabbits.bmp')),
            'objects' => SDL_CreateTextureFromSurface($canvas, SDL_LoadBMP(__DIR__ . '/../../assets/sprites/objects.bmp')),
            'numbers' => SDL_CreateTextureFromSurface($canvas, SDL_LoadBMP(__DIR__ . '/../../assets/sprites/numbers.bmp')),
        );

        $settings = array(
            'pogostick' => false,                       // gup('pogostick') == '1',
            'jetpack' => false,                         // gup('jetpack') == '1',
            'bunnies_in_space' => false,                // gup('space') == '1',
            'flies_enabled' => false,                   // gup('lordoftheflies') == '1',
            'blood_is_thicker_than_water' => false,     // gup('bloodisthickerthanwater') == '1',
            'no_gore' => false,                         // gup('nogore') == '1'
        );
        $this->muted = false;                                 // gup('nosound') == '1';

        $renderer = new Renderer($canvas, $img, $level);
        $objects = new Objects();
        $key_action_mappings = [];
        $this->keyboard = new Keyboard($key_action_mappings);
        $ai = new AI($this->keyboard);
        $animation = new Animation($renderer, $img, $objects);
        $this->sound_player = new Sound_Player($this->muted);
        $sfx = new Sfx($this->sound_player);
        $movement = new Movement($renderer, $img, $sfx, $objects, $settings);
        $this->game = new Game($movement, $ai, $animation, $renderer, $objects, [$this->keyboard, 'key_pressed'], $level, true);

        // this.scores = ko.observable([[]]);
        $this->game_state = Game_State::Not_Started;
    }

    public function set_current_keys($current_keys) {
        $this->keyboard->set_current_keys($current_keys);
    }

    private function set_game_state($game_state) {
        $this->game_state = $game_state;
    }

    private function unpause() {
        $this->set_game_state(Game_State::Playing);
        $this->sound_player->set_muted($this->muted);
        $this->game->start();
    }

    public function start() {
        // sfx.music();
        $this->unpause();
    }

    public function pump() {
        $this->game->pump();
    }

//     key_action_mappings["M"] = function () {
//         if (self.game_state() === Game_State.Playing) {
//             muted = !muted;
//             self.sound_player.toggle_sound();
//         }
//     }

//     key_action_mappings["P"] = function () {
//         switch(self.game_state()) {
//             case Game_State.Not_Started:
//                 self.start();
//                 break;
//             case Game_State.Paused:
//                 self.unpause();
//                 break;
//             case Game_State.Playing:
//                 self.pause();
//                 break;
//         }
//     };

//     document.onkeydown = keyboard.onKeyDown;
//     document.onkeyup = keyboard.onKeyUp;
}

