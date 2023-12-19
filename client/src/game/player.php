<?php

require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/../game/level.php";
require_once __DIR__ . "/../asset_data/default_levelmap.php";

$players = [];


class Player {
    public $player_index;
    public $keys;
    public $is_server;
    public $ai = false;
    public $action_left = false;
    public $action_up = false;
    public $action_right = false;
    public $enabled = true;
    public $dead_flag = false;
    public $bumps = false;
    public Point $x;
    public Point $y;
    public $direction = 0;
    public $jump_ready = false;
    public $jump_abort = false;
    public $in_water = false;
    public $anim = 0;
    public $frame = 0;
    public $frame_tick = 0;

    public function __construct($player_index, $keys, $is_server) {
        $this->player_index = $player_index;
        $this->keys = $keys;
        $this->is_server = $is_server;

        $this->x = new Point(0, 0);
        $this->y = new Point(0, 0);
    }

    public function set_anim($animIndex) {
        $this->anim = $animIndex;
        $this->frame = 0;
        $this->frame_tick = 0;
    }

    public function update_player_animation() {
        $this->frame_tick++;
        if ($this->frame_tick >= env['animation_data']->players[$this->anim]['frame'][$this->frame]['ticks']) {
            $this->frame++;
            if ($this->frame >= env['animation_data']->players[$this->anim]['num_frames']) {
                if ($this->anim != 6)
                    $this->frame = env['animation_data']->players[$this->anim]['restart_frame'];
                else
                    $this->position_player();
            }
            $this->frame_tick = 0;
        }
    }

    public function get_image() {
        return env['animation_data']->players[$this->anim]['frame'][$this->frame]['image'] + $this->direction * 9;
    }

    public function position_player() {
        global $players;
        $player_num = $this->player_index;

        do {
            $x = rnd(LEVEL_WIDTH);
            $y = rnd(LEVEL_HEIGHT);
            $good_place = true;
            if (GET_BAN_MAP($x, $y) != BAN_VOID) $good_place = false;
            else if (!(GET_BAN_MAP($x, $y + 1) == BAN_SOLID || GET_BAN_MAP($x, $y + 1) == BAN_ICE)) $good_place = false;
            else {
                foreach ($players as $p) {
                    if ($p != $this && $p->enabled) {
                        if (abs(($x << LEVEL_SCALE_FACTOR) - ($p->x->pos >> 16)) < 32 && abs(($y << LEVEL_SCALE_FACTOR) - ($p->y->pos >> 16)) < 32) {
                            $good_place = false;
                            break;
                        }
                    }
                }
            }
        } while (!$good_place);

        $this->x->pos = $x << 20;
        $this->y->pos = $y << 20;
        $this->x->velocity = $this->y->velocity = 0;
        $this->direction = 0;
        $this->jump_ready = 1;
        $this->in_water = 0;
        $this->set_anim(0);

        if ($this->is_server) {
            $this->dead_flag = 0;
        }
    }
}
