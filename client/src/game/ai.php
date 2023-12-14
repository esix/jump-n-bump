<?php

require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/level.php";
require_once __DIR__ . "/../game/player.php";


class AI {
    private $keyboard_state;

    public function __construct($keyboard_state) {
        $this->keyboard_state = $keyboard_state;
    }

    private function map_tile($x, $y) {
        return GET_BAN_MAP($x >> 4, $y >> 4);
    }

    private function nearest_player($i) {
        global $player;
        $nearest_distance = -1;
        $target = null;
        for ($j = 0; $j < env['JNB_MAX_PLAYERS']; $j++) {
            if ($i == $j || !$player[$j]->enabled) {
                continue;
            }
            $deltax = $player[$j]->x->pos - $player[$i]->x->pos;
            $deltay = $player[$j]->y->pos - $player[$i]->y->pos;
            $players_distance = $deltax * $deltax + $deltay * $deltay;

            if ($players_distance < $nearest_distance || $nearest_distance == -1) {
                $target = $player[$j];
                $nearest_distance = $players_distance;
            }
        }
        return $target;
    }

    public function cpu_move() {
        global $player;
        for ($i = 0; $i < env['JNB_MAX_PLAYERS']; $i++) {
            $current_player = $player[$i];
            if (!$current_player->enabled || !$current_player->ai) continue;
            $target = $this->nearest_player($i);
            if ($target == null) continue;
            $this->move($current_player, $target);
        }
    }

    private function move($current_player, $target) {
        $cur_posx = $current_player->x->pos >> 16;
        $cur_posy = $current_player->y->pos >> 16;
        $tar_posx = $target->x->pos >> 16;
        $tar_posy = $target->y->pos >> 16;

        $tar_dist_above = $cur_posy - $tar_posy;
        $tar_dist_right = $tar_posx - $cur_posx;
        $tar_is_right = $tar_dist_right > 0;

        $tar_above_nearby = $tar_dist_above > 0 && $tar_dist_above < 32
            && $tar_dist_right < 32 + 8 && $tar_dist_right > -32;

        $same_vertical_line = $tar_dist_right < 4 + 8 && $tar_dist_right > -4;

        $rm = $this->should_move_direction($tar_above_nearby, !$same_vertical_line, $tar_is_right);
        $lm = $this->should_move_direction($tar_above_nearby, !$same_vertical_line, !$tar_is_right);
        $jm = $this->should_jump($current_player, $cur_posx, $cur_posy, $tar_dist_above, $tar_above_nearby, $lm, $rm);

        $this->press_keys($current_player, $lm, $rm, $jm);
    }

    private function should_move_direction(bool $running_away, bool $allowed_to_chase, bool $dir_of_target) {
        return ($running_away xor $dir_of_target)
            && ($running_away or $allowed_to_chase); // Prevents "nervous" bunnies that keep changing direction as soon as the player does.
    }

    private function should_jump($current_player, $cur_posx, $cur_posy, $tar_dist_above, $tar_directly_above, $lm, $rm) {
        $already_jumping = $this->keyboard_state->key_pressed($current_player->keys[2]);
        $tile_below = $this->map_tile($cur_posx, $cur_posy + 16);
        $tile_above = $this->map_tile($cur_posx, $cur_posy - 8);
        $tile_heading_for = $this->map_tile($cur_posx - ($lm * 8) + ($rm * 16), $cur_posy + ($already_jumping * 8));
        $on_ground = $tile_below != BAN_VOID;
        $at_map_edge = $cur_posx <= 16 || $cur_posx + 8 >= 352 - 16;

        if ($on_ground && $already_jumping) {
            return false; //must release key before we can jump again
        }
        else if ($this->blocks_movement($tile_above)) {
            return false; // don't jump if there is something over it
        }
        else if ($this->blocks_movement($tile_heading_for) && !$at_map_edge) {
            return true;   // if there is something on the way, jump over it
        }
        else if ($this->blocks_movement($tile_heading_for) && $already_jumping) {
            return true;   // this makes it possible to jump over 2 tiles
        }
        else {
            $could_get_above_target = $tar_dist_above >= 0 && $tar_dist_above < 32;
            return !$tar_directly_above && $could_get_above_target;
        }
    }

    private function blocks_movement($tile_type) {
        return $tile_type != BAN_WATER && $tile_type != BAN_VOID;
    }

    private function press_keys($p, $lm, $rm, $jm) {
        if ($lm) {
            $this->keyboard_state->addkey($p, 0);
        } else {
            $this->keyboard_state->delkey($p, 0);
        }

        if ($rm) {
            $this->keyboard_state->addkey($p, 1);
        } else {
            $this->keyboard_state->delkey($p, 1);
        }

        if ($jm) {
            $this->keyboard_state->addkey($p, 2);
        } else {
            $this->keyboard_state->delkey($p, 2);
        }
    }
}
