<?php

require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/../game/player.php";
require_once __DIR__ . "/../game/level.php";
require_once __DIR__ . "/../game/movement.php";
require_once __DIR__ . "/../game/player.php";


const FPS = 60;


class Game {
    private $movement;
    private $ai;
    private $animation;
    private $renderer;
    private $objects;
    private $key_pressed;
    private $level;
    private $is_server;

    private $next_time;
    private $playing;

    public function __construct($movement, $ai, $animation, $renderer, $objects, $key_pressed, $level, $is_server) {
        $this->movement = $movement;
        $this->ai = $ai;
        $this->animation = $animation;
        $this->renderer = $renderer;
        $this->objects = $objects;
        $this->key_pressed = $key_pressed;
        $this->level = $level;
        $this->is_server = $is_server;

        $this->next_time = 0;
        $this->playing = false;
        $this->reset_players();
        $this->reset_level();
    }

    private function reset_players() {
        global $player;
        $player = [
            new Player(0, [SDL_SCANCODE_LEFT, SDL_SCANCODE_RIGHT, SDL_SCANCODE_UP], $this->is_server),
            new Player(1, [SDL_SCANCODE_A, SDL_SCANCODE_D, SDL_SCANCODE_W], $this->is_server),
            new Player(2, [SDL_SCANCODE_KP_4, SDL_SCANCODE_KP_6, SDL_SCANCODE_KP_8], $this->is_server),
            new Player(3, [SDL_SCANCODE_J, SDL_SCANCODE_L, SDL_SCANCODE_I], $this->is_server)
        ];
        $player[3]->ai = true;
    }

    private function reset_level() {
        global $player;
        SET_BAN_MAP($this->level['ban_map']);
        $this->objects->reset_objects();

        for ($c1 = 0; $c1 < env['JNB_MAX_PLAYERS']; $c1++) {
            if ($player[$c1]->enabled) {
                $player[$c1]->bumps = 0;
                for ($c2 = 0; $c2 < env['JNB_MAX_PLAYERS']; $c2++) {
                    $player[$c1]->bumped[$c2] = 0;
                }
                $player[$c1]->position_player($c1);
            }
        }
    }

    private function timeGetTime() {
        return floor(microtime(true) * 1000);
    }

    private function update_player_actions() {
        global $player;
        for ($i = 0; $i != count($player); ++$i) {
            $player[$i]->action_left = call_user_func($this->key_pressed, $player[$i]->keys[0]);
            $player[$i]->action_right = call_user_func($this->key_pressed, $player[$i]->keys[1]);
            $player[$i]->action_up = call_user_func($this->key_pressed, $player[$i]->keys[2]);
        }
    }

    private function steer_players() {
        global $player;
        $this->ai->cpu_move();
        $this->update_player_actions();
        for ($playerIndex = 0; $playerIndex != count($player); ++$playerIndex) {
            $p = $player[$playerIndex];
            if ($p->enabled) {
                if (!$p->dead_flag) {
                    $this->movement->steer_player($p);
                }
                $p->update_player_animation();
            }
        }
    }

    private function game_iteration() {
        $this->steer_players();
        $this->movement->collision_check();
        $this->animation->update_object();
        $this->renderer->draw();
    }

    public function pump() {
        if (!$this->playing) return;

        $now = $this->timeGetTime();
        $time_diff = $this->next_time - $now;

        while ($time_diff <= 0) {
//             echo "PUMP" . $this->next_time . "\n";
            $this->game_iteration();
            $this->next_time += (1000 / FPS);
            $time_diff = $this->next_time - $now;
        }
    }

    public function start() {
        $this->next_time = $this->timeGetTime() + 1000;
        $this->playing = true;
        $this->pump();
    }

    public function pause() {
        $this->playing = false;
    }
}
