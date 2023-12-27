<?php

require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/../game/player.php";
require_once __DIR__ . "/../game/level.php";
require_once __DIR__ . "/../game/movement.php";
require_once __DIR__ . "/../game/player.php";


const FPS = 60;


class Game {
    private $movement;
    private $animation;
    private $renderer;
    private $objects;
    private $level;

    private $next_time;
    private $playing;

    public function __construct($movement, $animation, $renderer, $objects, $level) {
        $this->movement = $movement;
        $this->animation = $animation;
        $this->renderer = $renderer;
        $this->objects = $objects;
        $this->level = $level;

        $this->next_time = 0;
        $this->playing = false;
        $this->reset_players();
        $this->reset_level();
    }

    private function reset_players() {
        global $players;
        $players = [
            new Player(0),
            new Player(1),
            new Player(2),
            new Player(3)
        ];
    }

    private function reset_level() {
        global $players;
        SET_BAN_MAP($this->level['ban_map']);
        $this->objects->reset_objects();

        foreach ($players as $p) {
            if ($p->enabled) {
//                 $p->bumps = 0;
                $p->position_player();
            }
        }
    }

    private function timeGetTime() {
        return floor(microtime(true) * 1000);
    }

    private function update_player_actions($current_keys) {
        global $players, $player_id;
        foreach ($players as $p) {
            if ($p->id == $player_id) {                                                             // current player
                // 0 => [SDL_SCANCODE_LEFT, SDL_SCANCODE_RIGHT, SDL_SCANCODE_UP]),
                // 1 => [SDL_SCANCODE_A, SDL_SCANCODE_D, SDL_SCANCODE_W]),
                // 2 => [SDL_SCANCODE_KP_4, SDL_SCANCODE_KP_6, SDL_SCANCODE_KP_8]),
                // 3 => [SDL_SCANCODE_J, SDL_SCANCODE_L, SDL_SCANCODE_I])
                $p->action_left = $current_keys[SDL_SCANCODE_LEFT] ?? false;
                $p->action_right = $current_keys[SDL_SCANCODE_RIGHT] ?? false;
                $p->action_up = $current_keys[SDL_SCANCODE_UP] ?? false;
            }
        }
    }

    private function steer_players($current_keys) {
        global $players;
        $this->update_player_actions($current_keys);
        foreach ($players as $p) {
            if ($p->enabled) {
                if (!$p->dead_flag) {
                    $this->movement->steer_player($p);
                }
                $p->update_player_animation();
            }
        }
    }

    private function game_iteration($current_keys) {
        $this->steer_players($current_keys);
        $this->movement->collision_check();
        $this->animation->update_object();
        $this->renderer->draw();
    }

    public function pump($current_keys) {
        if (!$this->playing) return;

        $now = $this->timeGetTime();
        $time_diff = $this->next_time - $now;

        while ($time_diff <= 0) {
            $this->game_iteration($current_keys);
            $this->next_time += (1000 / FPS);
            $time_diff = $this->next_time - $now;
        }
    }

    public function start() {
        $this->next_time = $this->timeGetTime() + 1000;
        $this->playing = true;
        // $this->pump();
    }

    public function pause() {
        $this->playing = false;
    }
}
