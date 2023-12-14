<?php

require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/../game/level.php";
require_once __DIR__ . "/../asset_data/default_levelmap.php";

$player = [];


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
    public $bumped = [];
    public $x;
    public $y;
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
                    $this->position_player($this->player_index);
            }
            $this->frame_tick = 0;
        }
    }

    public function get_image() {
        return env['animation_data']->players[$this->anim]['frame'][$this->frame]['image'] + $this->direction * 9;
    }

    public function position_player($player_num) {
        global $player;

        while (1) {
            while (1) {
                $s1 = rnd(LEVEL_WIDTH);
                $s2 = rnd(LEVEL_HEIGHT);
                if (GET_BAN_MAP($s1, $s2) == BAN_VOID && (GET_BAN_MAP($s1, $s2 + 1) == BAN_SOLID || GET_BAN_MAP($s1, $s2 + 1) == BAN_ICE))
                    break;
            }
            for ($c1 = 0; $c1 < env['JNB_MAX_PLAYERS']; $c1++) {
                if ($c1 != $player_num && $player[$c1]->enabled) {
                    if (abs(($s1 << LEVEL_SCALE_FACTOR) - ($player[$c1]->x->pos >> 16)) < 32 && abs(($s2 << LEVEL_SCALE_FACTOR) - ($player[$c1]->y->pos >> 16)) < 32)
                        break;
                }
            }
            if ($c1 == env['JNB_MAX_PLAYERS']) {
                $player[$player_num]->x->pos = $s1 << 20;
                $player[$player_num]->y->pos = $s2 << 20;
                $player[$player_num]->x->velocity = $player[$player_num]->y->velocity = 0;
                $player[$player_num]->direction = 0;
                $player[$player_num]->jump_ready = 1;
                $player[$player_num]->in_water = 0;
                $player[$player_num]->set_anim(0);

                if ($this->is_server) {
                    $player[$player_num]->dead_flag = 0;
                }

                break;
            }
        }
    }
}
