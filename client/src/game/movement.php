<?php

require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/../asset_data/default_levelmap.php";
require_once __DIR__ . "/../game/level.php";
require_once __DIR__ . "/../game/player_pair.php";
require_once __DIR__ . "/../game/player.php";


class Movement {
    private $renderer;
    private $img;
    private $sfx;
    private $objects;
    private $settings;

    public function __construct($renderer, $img, $sfx, $objects, $settings) {
        $this->renderer = $renderer;
        $this->img = $img;
        $this->sfx = $sfx;
        $this->objects = $objects;
        $this->settings = $settings;
    }


    public function steer_player($p) {
        $s1 = 0;
        $s2 = 0;
        if ($p->action_left && !($p->action_right && $p->direction == 0)) {
            $this->player_action_left($p);
        } else if ($p->action_right) {
            $this->player_action_right($p);
        } else {
            $s1 = ($p->x->pos >> 16);
            $s2 = ($p->y->pos >> 16);
            $below_left = GET_BAN_MAP_XY($s1, $s2 + 16);
            $below = GET_BAN_MAP_XY($s1 + 8, $s2 + 16);
            $below_right = GET_BAN_MAP_XY($s1 + 15, $s2 + 16);
            if ($below == BAN_SOLID || $below == BAN_SPRING || ((($below_left == BAN_SOLID || $below_left == BAN_SPRING) && $below_right != BAN_ICE) || ($below_left != BAN_ICE && ($below_right == BAN_SOLID || $below_right == BAN_SPRING)))) {
                if ($p->x->velocity < 0) {
                    $p->x->velocity += 16384;
                    if ($p->x->velocity > 0)
                        $p->x->velocity = 0;
                } else {
                    $p->x->velocity -= 16384;
                    if ($p->x->velocity < 0)
                        $p->x->velocity = 0;
                }
                if ($p->x->velocity != 0 && GET_BAN_MAP_XY(($s1 + 8), $s2 + 16) == BAN_SOLID) {
                    $this->objects->add(Objects::SMOKE, ($p->x->pos >> 16) + 2 + rnd(9), ($p->y->pos >> 16) + 13 + rnd(5), 0, -16384 - rnd(8192), Objects::ANIM_SMOKE, 0);
                }
            }
            if ($p->anim == 1) {
                $p->set_anim(0);
            }
        }

        if ($this->settings['jetpack'] == 0) {
            /* no jetpack */
            if ($this->settings['pogostick'] == 1 || ($p->jump_ready == 1 && $p->action_up)) {
                $s1 = ($p->x->pos >> 16);
                $s2 = ($p->y->pos >> 16);
                if ($s2 < -16)
                    $s2 = -16; //Allow player to jump off screen but not negative overflow if using jetpack
                /* jump */
                if (GET_BAN_MAP_XY($s1, ($s2 + 16)) == BAN_SOLID || GET_BAN_MAP_XY($s1, ($s2 + 16)) == BAN_ICE || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 16)) == BAN_SOLID || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 16)) == BAN_ICE) {
                    $p->y->velocity = -280000;
                    $p->set_anim(2);
                    $p->jump_ready = 0;
                    $p->jump_abort = 1;
                    if ($this->settings['pogostick'] == 0)
                        $this->sfx->jump();
                    else
                        $this->sfx->spring();
                }
                /* jump out of water */
                if (GET_BAN_MAP_IN_WATER($s1, $s2)) {
                    $p->y->velocity = -196608;
                    $p->in_water = 0;
                    $p->set_anim(2);
                    $p->jump_ready = 0;
                    $p->jump_abort = 1;
                    if ($this->settings['pogostick'] == 0)
                        $this->sfx->jump();
                    else
                        $this->sfx->spring();
                }
            }
            /* fall down by gravity */
            if ($this->settings['pogostick'] == 0 && (!$p->action_up)) {
                $p->jump_ready = 1;
                if ($p->in_water == 0 && $p->y->velocity < 0 && $p->jump_abort == 1) {
                    if ($this->settings['bunnies_in_space'] == 0)
                        /* normal gravity */
                        $p->y->velocity += 32768;
                    else
                        /* light gravity */
                        $p->y->velocity += 16384;
                    if ($p->y->velocity > 0)
                        $p->y->velocity = 0;
                }
            }
        } else {
            /* with jetpack */
            if ($p->action_up) {
                $p->y->velocity -= 16384;
                if ($p->y->velocity < -400000)
                    $p->y->velocity = -400000;
                if (GET_BAN_MAP_IN_WATER($s1, $s2))
                    $p->in_water = 0;
                if (rnd(100) < 50)
                    $this->objects->add(Objects::SMOKE, ($p->x->pos >> 16) + 6 + rnd(5), ($p->y->pos >> 16) + 10 + rnd(5), 0, 16384 + rnd(8192), Objects::ANIM_SMOKE, 0);
            }
        }

        $p->x->pos += $p->x->velocity;
        if (($p->x->pos >> 16) < 0) {
            $p->x->pos = 0;
            $p->x->velocity = 0;
        }
        if (($p->x->pos >> 16) + 15 > 351) {
            $p->x->pos = 336 << 16;
            $p->x->velocity = 0;
        }
        {
            if ($p->y->pos > 0) {
                $s2 = ($p->y->pos >> 16);
            } else {
                /* check top line only */
                $s2 = 0;
            }

            $s1 = ($p->x->pos >> 16);
            if (GET_BAN_MAP_XY($s1, $s2) == BAN_SOLID || GET_BAN_MAP_XY($s1, $s2) == BAN_ICE || GET_BAN_MAP_XY($s1, $s2) == BAN_SPRING || GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_SOLID || GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_ICE || GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_SPRING) {
                $p->x->pos = ((($s1 + 16) & 0xfff0)) << 16;
                $p->x->velocity = 0;
            }

            $s1 = ($p->x->pos >> 16);
            if (GET_BAN_MAP_XY(($s1 + 15), $s2) == BAN_SOLID || GET_BAN_MAP_XY(($s1 + 15), $s2) == BAN_ICE || GET_BAN_MAP_XY(($s1 + 15), $s2) == BAN_SPRING || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_SOLID || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_ICE || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_SPRING) {
                $p->x->pos = ((($s1 + 16) & 0xfff0) - 16) << 16;
                $p->x->velocity = 0;
            }
        }

        $p->y->pos += $p->y->velocity;

        $s1 = ($p->x->pos >> 16);
        $s2 = ($p->y->pos >> 16);
        if (GET_BAN_MAP_XY(($s1 + 8), ($s2 + 15)) == BAN_SPRING || ((GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_SPRING && GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) != BAN_SOLID) || (GET_BAN_MAP_XY($s1, ($s2 + 15)) != BAN_SOLID && GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_SPRING))) {
            $p->y->pos = (($p->y->pos >> 16) & 0xfff0) << 16;
            $p->y->velocity = -400000;
            $p->set_anim(2);
            $p->jump_ready = 0;
            $p->jump_abort = 0;
            for ($c2 = 0; $c2 < env['MAX_OBJECTS']; $c2++) {
                $obj = $this->objects->objects[$c2];
                if ($obj->used == 1 && $obj->type == Objects::SPRING) {
                    if (GET_BAN_MAP_XY(($s1 + 8), ($s2 + 15)) == BAN_SPRING) {
                        if (($obj->x->pos >> 20) == (($s1 + 8) >> LEVEL_SCALE_FACTOR) && ($obj->y->pos >> 20) == (($s2 + 15) >> LEVEL_SCALE_FACTOR)) {
                            $this->start_anim($obj);
                            break;
                        }
                    } else if (GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_SPRING) {
                            if (($obj->x->pos >> 20) == ($s1 >> LEVEL_SCALE_FACTOR) && ($obj->y->pos >> 20) == (($s2 + 15) >> LEVEL_SCALE_FACTOR)) {
                                $this->start_anim($obj);
                                break;
                            }
                    } else if (GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_SPRING) {
                        if (($obj->x->pos >> 20) == (($s1 + 15) >> LEVEL_SCALE_FACTOR) && ($obj->y->pos >> 20) == (($s2 + 15) >> LEVEL_SCALE_FACTOR)) {
                            $this->start_anim($obj);
                            break;
                        }
                    }
                }
            }
            $this->sfx->spring();
        }
        $s1 = ($p->x->pos >> 16);
        $s2 = ($p->y->pos >> 16);
        if ($s2 < 0)
            $s2 = 0;
        if (GET_BAN_MAP_XY($s1, $s2) == BAN_SOLID || GET_BAN_MAP_XY($s1, $s2) == BAN_ICE || GET_BAN_MAP_XY($s1, $s2) == BAN_SPRING || GET_BAN_MAP_XY(($s1 + 15), $s2) == BAN_SOLID || GET_BAN_MAP_XY(($s1 + 15), $s2) == BAN_ICE || GET_BAN_MAP_XY(($s1 + 15), $s2) == BAN_SPRING) {
            $p->y->pos = ((($s2 + 16) & 0xfff0)) << 16;
            $p->y->velocity = 0;
            $p->set_anim(0);
        }
        $s1 = ($p->x->pos >> 16);
        $s2 = ($p->y->pos >> 16);
        if ($s2 < 0)
            $s2 = 0;
        if (GET_BAN_MAP_XY(($s1 + 8), ($s2 + 8)) == BAN_WATER) {
            if ($p->in_water == 0) {
                /* falling into water */
                $p->in_water = 1;
                $p->set_anim(4);
                if ($p->y->velocity >= 32768) {
                    $this->objects->add(Objects::SPLASH, ($p->x->pos >> 16) + 8, (($p->y->pos >> 16) & 0xfff0) + 15, 0, 0, Objects::ANIM_SPLASH, 0);
                    $this->sfx->splash();
                }
            }
            /* slowly move up to water surface */
            $p->y->velocity -= 1536;
            if ($p->y->velocity < 0 && $p->anim != 5) {
                $p->set_anim(5);
            }
            if ($p->y->velocity < -65536)
                $p->y->velocity = -65536;
            if ($p->y->velocity > 65535)
                $p->y->velocity = 65535;
            if (GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_SOLID || GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_ICE || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_SOLID || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_ICE) {
                $p->y->pos = ((($s2 + 16) & 0xfff0) - 16) << 16;
                $p->y->velocity = 0;
            }
        } else if (GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_SOLID || GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_ICE || GET_BAN_MAP_XY($s1, ($s2 + 15)) == BAN_SPRING || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_SOLID || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_ICE || GET_BAN_MAP_XY(($s1 + 15), ($s2 + 15)) == BAN_SPRING) {
            $p->in_water = 0;
            $p->y->pos = ((($s2 + 16) & 0xfff0) - 16) << 16;
            $p->y->velocity = 0;
            if ($p->anim != 0 && $p->anim != 1) {
                $p->set_anim(0);
            }
        } else {
            if ($p->in_water == 0) {
                if ($this->settings['bunnies_in_space'] == 0)
                    $p->y->velocity += 12288;
                else
                    $p->y->velocity += 6144;
                if ($p->y->velocity > 327680)
                    $p->y->velocity = 327680;
            } else {
                $p->y->pos = ($p->y->pos & 0xffff0000) + 0x10000;
                $p->y->velocity = 0;
            }
            $p->in_water = 0;
        }
        if ($p->y->velocity > 36864 && $p->anim != 3 && $p->in_water == 0) {
            $p->set_anim(3);
        }
    }

    public function collision_check() {
        global $player;
        /* collision check */
        for ($c1 = 0; $c1 < 3; $c1++) {
            for ($c2 = $c1 + 1; $c2 < 4; $c2++) {
                $pair = new Player_Pair($player[$c1], $player[$c2], $this->sfx, $this->renderer, $this->objects, $this->img, $this->settings);
                $pair->collision_check();
            }
        }
    }

    private function start_anim($obj) {
        $obj->frame = 0;
        $obj->ticks = env['animation_data']->objects[$obj->anim]['frame'][$obj->frame]['ticks'];
        $obj->image = env['animation_data']->objects[$obj->anim]['frame'][$obj->frame]['image'];
    }

    private function player_action_left($p) {
        $sx = ($p->x->pos >> 16);
        $sy = ($p->y->pos >> 16);
        $below_left = GET_BAN_MAP_XY($sx, $sy + 16);
        $below = GET_BAN_MAP_XY($sx + 8, $sy + 16);
        $below_right = GET_BAN_MAP_XY($sx + 15, $sy + 16);
        $moving_right = $p->x->velocity > 0;
        $is_ice_below = $below == BAN_ICE || ($below_left != BAN_SOLID && $below_right == BAN_ICE) || ($below_left == BAN_ICE && $below_right != BAN_SOLID);
        $acceleration = $is_ice_below ? 1 : 8;

        if ($moving_right) {
            $p->x->velocity -= (1024 * $acceleration);
            if ($p->in_water == 0 && $below == BAN_SOLID) {
                $this->objects->add(Objects::SMOKE, ($p->x->pos >> 16) + 2 + rnd(9), ($p->y->pos >> 16) + 13 + rnd(5), 0, -16384 - rnd(8192), Objects::ANIM_SMOKE, 0);
            }
        } else {
            $p->x->velocity -= (768 * $acceleration);
        }

        if ($p->x->velocity < -98304) {
            $p->x->velocity = -98304;
        }
        $p->direction = 1;
        if ($p->anim == 0) {
            $p->set_anim(1);
        }
    }

    private function player_action_right($p) {
        $sx = ($p->x->pos >> 16);
        $sy = ($p->y->pos >> 16);
        $below_left = GET_BAN_MAP_XY($sx, $sy + 16);
        $below = GET_BAN_MAP_XY($sx + 8, $sy + 16);
        $below_right = GET_BAN_MAP_XY($sx + 15, $sy + 16);
        $moving_left = $p->x->velocity < 0;
        $is_ice_below = $below == BAN_ICE || ($below_left != BAN_SOLID && $below_right == BAN_ICE) || ($below_left == BAN_ICE && $below_right != BAN_SOLID);
        $acceleration = $is_ice_below ? 1 : 8;

        if ($moving_left) {
            $p->x->velocity += (1024 * $acceleration);
            if ($p->in_water == 0 && $below == BAN_SOLID) {
                $this->objects->add(Objects::SMOKE, ($p->x->pos >> 16) + 2 + rnd(9), ($p->y->pos >> 16) + 13 + rnd(5), 0, -16384 - rnd(8192), Objects::ANIM_SMOKE, 0);
            }
        } else {
            $p->x->velocity += (768 * $acceleration);
        }

        if ($p->x->velocity > 98304) {
            $p->x->velocity = 98304;
        }
        $p->direction = 0;
        if ($p->anim == 0) {
            $p->set_anim(1);
        }
    }
}
