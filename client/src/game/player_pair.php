<?php

require_once __DIR__ . "/../asset_data/number_gobs.php";


class Player_Pair {
    private $first;
    private $second;
    private $sfx;
    private $renderer;
    private $objects;
    private $img;
    private $settings;

    public function __construct($first, $second, $sfx, $renderer, $objects, $img, $settings) {
        $this->first = $first;
        $this->second = $second;
        $this->sfx = $sfx;
        $this->renderer = $renderer;
        $this->objects = $objects;
        $this->img = $img;
        $this->settings = $settings;
    }

    public function highest() {
        return $this->first->y->pos < $this->second->y->pos ? $this->first : $this->second;
    }

    public function lowest() {
        return $this->first === $this->highest() ? $this->second : $this->first;
    }

    public function leftmost() {
        return $this->first->x->pos < $this->second->x->pos ? $this->first : $this->second;
    }

    public function rightmost() {
        return $this->first == $this->leftmost() ? $this->second : $this->first;
    }

    public function collision_check() {
        if ($this->first->enabled && $this->second->enabled) {
            if ($this->touching()) {
                if ($this->not_same_height()) {
                    $this->player_kill($this->highest(), $this->lowest());
                } else {
                    $this->repel_each_other($this->leftmost(), $this->rightmost());
                }
            }
        }
    }

    private function touching() {
        return abs($this->first->x->pos - $this->second->x->pos) < 0xC0000 && abs($this->first->y->pos - $this->second->y->pos) < 0xC0000;
    }

    private function not_same_height() {
        return (abs($this->first->y->pos - $this->second->y->pos) >> 16) > 5;
    }

    private function repel_each_other($left_player, $right_player) {
        if ($right_player->x->velocity > 0)
            $left_player->x->pos = $right_player->x->pos - 0xC0000;
        else if ($left_player->x->velocity < 0)
            $right_player->x->pos = $left_player->x->pos + 0xC0000;
        else {
            $left_player->x->pos -= $left_player->x->velocity;
            $right_player->x->pos -= $right_player->x->velocity;
        }
        $l1 = $left_player->x->velocity;
        $left_player->x->velocity = $right_player->x->velocity;
        $right_player->x->velocity = $l1;
        if ($right_player->x->velocity < 0)
            $right_player->x->velocity = -$right_player->x->velocity;
        if ($left_player->x->velocity > 0)
            $left_player->x->velocity = -$left_player->x->velocity;
    }

    private function player_kill($killer, $victim) {
        $killer->y->velocity = -$killer->y->velocity;
        if ($killer->y->velocity > -262144)
            $killer->y->velocity = -262144;
        $killer->jump_abort = true;
        $victim->dead_flag = true;
        if ($victim->anim != 6) {
            $victim->set_anim(6);
            if (!$this->settings['no_gore']) {
                $this->objects->add_gore($victim->x->pos, $victim->y->pos, $victim->player_index);
            }
            $this->sfx->death();
            $killer->bumps++;
            $s1 = $killer->bumps % 100;
            if ($s1 % 10 == 0) {
                $this->renderer->add_leftovers(360, 34 + $killer->player_index * 64, $this->img['numbers'], number_gobs[floor($s1 / 10) % 10]);
            }
            $this->renderer->add_leftovers(376, 34 + $killer->player_index * 64, $this->img['numbers'], number_gobs[$s1 % 10]);
        }
    }
}
