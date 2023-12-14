<?php

require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/../game/level.php";
require_once __DIR__ . "/../asset_data/object_gobs.php";

class Animation {
    private $renderer;
    private $img;
    private $objects;

    public function __construct($renderer, $img, $objects) {
        $this->renderer = $renderer;
        $this->img = $img;
        $this->objects = $objects;
    }

    private function advance_frame($obj, $pause_at_end, $loop) {
        $obj->frame++;
        if ($obj->frame >= env['animation_data']->objects[$obj->anim]['num_frames']) {
            if ($pause_at_end) {
                $obj->frame--;
                $obj->ticks = env['animation_data']->objects[$obj->anim]['frame'][$obj->frame]['ticks'];
            }
            else if ($loop) {
                $obj->frame = env['animation_data']->objects[$obj->anim]['restart_frame'];
            }
            else {
                $obj->used = false;
            }
        } else {
            $obj->ticks = env['animation_data']->objects[$obj->anim]['frame'][$obj->frame]['ticks'];
            $obj->image = env['animation_data']->objects[$obj->anim]['frame'][$obj->frame]['image'];
        }
    }

    private function tick($obj, $pause_at_end, $loop) {
        $obj->ticks--;
        if ($obj->ticks <= 0) {
            $this->advance_frame($obj, $pause_at_end, $loop);
        }
        if ($obj->used)
            $this->renderer->add_pob($obj->x->pos >> 16, $obj->y->pos >> 16, $this->img['objects'], object_gobs[$obj->image]);
    }

    private function start_anim($obj, $anim) {
        $obj->anim = $anim;
        $obj->frame = 0;
        $obj->ticks = env['animation_data']->objects[$obj->anim]['frame'][$obj->frame]['ticks'];
        $obj->image = env['animation_data']->objects[$obj->anim]['frame'][$obj->frame]['image'];
    }

    private function map_tile($obj) {
        return GET_BAN_MAP($obj->x->pos >> 20, $obj->y->pos >> 20);
    }

    private function update_butterfly_position($obj, $dimension, $min, $max) {
        if ($dimension->acceleration < -1024)
            $dimension->acceleration = -1024;
        if ($dimension->acceleration > 1024)
            $dimension->acceleration = 1024;
        $dimension->velocity += $dimension->acceleration;
        if ($dimension->velocity < -32768)
            $dimension->velocity = -32768;
        if ($dimension->velocity > 32768)
            $dimension->velocity = 32768;
        $dimension->pos += $dimension->velocity;
        if (($dimension->pos >> 16) < $min) {
            $dimension->pos = $min << 16;
            $dimension->velocity = -$dimension->velocity >> 2;
            $dimension->acceleration = 0;
        } else if (($dimension->pos >> 16) > $max) {
            $dimension->pos = $max << 16;
            $dimension->velocity = -$dimension->velocity >> 2;
            $dimension->acceleration = 0;
        }
        if ($this->map_tile($obj) != BAN_VOID) {
            if ($dimension->velocity < 0) {
                $dimension->pos = ((($dimension->pos >> 16) + 16) & 0xfff0) << 16;
            } else {
                $dimension->pos = (((($dimension->pos >> 16) - 16) & 0xfff0) + 15) << 16;
            }
            $dimension->velocity = -$dimension->velocity >> 2;
            $dimension->acceleration = 0;
        }
    }

    private function add_flesh_trace($obj, $frame) {
        $this->objects->add(Objects::FLESH_TRACE, $obj->x->pos >> 16, $obj->y->pos >> 16, 0, 0, Objects::ANIM_FLESH_TRACE, $frame);
    }

    public function update_object() {
        $s1 = 0;

        for ($c1 = 0; $c1 < env['MAX_OBJECTS']; $c1++) {
            $obj = $this->objects->objects[$c1];
            if ($obj->used) {
                switch ($obj->type) {
                    case Objects::SPRING:
                        $this->tick($obj, true, false);
                        break;
                    case Objects::SPLASH:
                    case Objects::SMOKE:
                    case Objects::FLESH_TRACE:
                        $this->tick($obj, false, false);
                        break;
                    case Objects::YEL_BUTFLY:
                    case Objects::PINK_BUTFLY:
                        $obj->x->acceleration += rnd(128) - 64;
                        $this->update_butterfly_position($obj, $obj->x, 16, 350);
                        $obj->y->acceleration += rnd(64) - 32;
                        $this->update_butterfly_position($obj, $obj->y, 0, 255);

                        //Set animation based on direction of movement - TODO give object ownership of its left and right animation to deduplicate this
                        if ($obj->type == Objects::YEL_BUTFLY) {
                            if ($obj->x->velocity < 0 && $obj->anim != Objects::ANIM_YEL_BUTFLY_LEFT) {
                                $this->start_anim($obj, Objects::ANIM_YEL_BUTFLY_LEFT);
                            } else if ($obj->x->velocity > 0 && $obj->anim != Objects::ANIM_YEL_BUTFLY_RIGHT) {
                                $this->start_anim($obj, Objects::ANIM_YEL_BUTFLY_RIGHT);
                            }
                        } else {
                            if ($obj->x->velocity < 0 && $obj->anim != Objects::ANIM_PINK_BUTFLY_LEFT) {
                                $this->start_anim($obj, Objects::ANIM_PINK_BUTFLY_LEFT);
                            } else if ($obj->x->velocity > 0 && $obj->anim != Objects::ANIM_PINK_BUTFLY_RIGHT) {
                                $this->start_anim($obj, Objects::ANIM_PINK_BUTFLY_RIGHT);
                            }
                        }
                        $this->tick($obj, false, true);
                        break;
                    case Objects::FUR:
                        if (rnd(100) < 30)
                            $this->add_flesh_trace($obj, 0);
                        if ($this->map_tile($obj) == BAN_VOID) {
                            $obj->y->velocity += 3072;
                            if ($obj->y->velocity > 196608)
                                $obj->y->velocity = 196608;
                        } else if ($this->map_tile($obj) == BAN_WATER) {
                            if ($obj->x->velocity < 0) {
                                if ($obj->x->velocity < -65536)
                                    $obj->x->velocity = -65536;
                                $obj->x->velocity += 1024;
                                if ($obj->x->velocity > 0)
                                    $obj->x->velocity = 0;
                            } else {
                                if ($obj->x->velocity > 65536)
                                    $obj->x->velocity = 65536;
                                $obj->x->velocity -= 1024;
                                if ($obj->x->velocity < 0)
                                    $obj->x->velocity = 0;
                            }
                            $obj->y->velocity += 1024;
                            if ($obj->y->velocity < -65536)
                                $obj->y->velocity = -65536;
                            if ($obj->y->velocity > 65536)
                                $obj->y->velocity = 65536;
                        }
                        $obj->x->pos += $obj->x->velocity;
                        if (($obj->y->pos >> 16) > 0 && ($this->map_tile($obj) == BAN_SOLID || $this->map_tile($obj) == BAN_ICE)) {
                            if ($obj->x->velocity < 0) {
                                $obj->x->pos = ((($obj->x->pos >> 16) + 16) & 0xfff0) << 16;
                                $obj->x->velocity = -$obj->x->velocity >> 2;
                            } else {
                                $obj->x->pos = (((($obj->x->pos >> 16) - 16) & 0xfff0) + 15) << 16;
                                $obj->x->velocity = -$obj->x->velocity >> 2;
                            }
                        }
                        $obj->y->pos += $obj->y->velocity;
                        if (($obj->x->pos >> 16) < -5 || ($obj->x->pos >> 16) > 405 || ($obj->y->pos >> 16) > 260)
                            $obj->used = false;
                        if (($obj->y->pos >> 16) > 0 && ($this->map_tile($obj) != BAN_VOID)) {
                            if ($obj->y->velocity < 0) {
                                if ($this->map_tile($obj) != BAN_WATER) {
                                    $obj->y->pos = ((($obj->y->pos >> 16) + 16) & 0xfff0) << 16;
                                    $obj->x->velocity >>= 2;
                                    $obj->y->velocity = -$obj->y->velocity >> 2;
                                }
                            } else {
                                if ($this->map_tile($obj) == BAN_SOLID) {
                                    if ($obj->y->velocity > 131072) {
                                        $obj->y->pos = (((($obj->y->pos >> 16) - 16) & 0xfff0) + 15) << 16;
                                        $obj->x->velocity >>= 2;
                                        $obj->y->velocity = -$obj->y->velocity >> 2;
                                    } else
                                        $obj->used = false;
                                } else if ($this->map_tile($obj) == BAN_ICE) {
                                    $obj->y->pos = (((($obj->y->pos >> 16) - 16) & 0xfff0) + 15) << 16;
                                    if ($obj->y->velocity > 131072)
                                        $obj->y->velocity = -$obj->y->velocity >> 2;
                                    else
                                        $obj->y->velocity = 0;
                                }
                            }
                        }
                        if ($obj->x->velocity < 0 && $obj->x->velocity > -16384)
                            $obj->x->velocity = -16384;
                        if ($obj->x->velocity > 0 && $obj->x->velocity < 16384)
                            $obj->x->velocity = 16384;
                        if ($obj->used) {
                            $s1 = floor(atan2($obj->y->velocity, $obj->x->velocity) * 4 / M_PI);
                            if ($s1 < 0)
                                $s1 += 8;
                            if ($s1 < 0)
                                $s1 = 0;
                            if ($s1 > 7)
                                $s1 = 7;
                            $this->renderer->add_pob($obj->x->pos >> 16, $obj->y->pos >> 16, $this->img['objects'], object_gobs[$obj->frame + $s1]);
                        }
                        break;
                    case Objects::FLESH:
                        if (rnd(100) < 30) {
                            if ($obj->frame == 76)
                                $this->add_flesh_trace($obj, 1);
                            else if ($obj->frame == 77)
                                $this->add_flesh_trace($obj, 2);
                            else if ($obj->frame == 78)
                                $this->add_flesh_trace($obj, 3);
                        }
                        if ($this->map_tile($obj) == BAN_VOID) {
                            $obj->y->velocity += 3072;
                            if ($obj->y->velocity > 196608)
                                $obj->y->velocity = 196608;
                        } else if ($this->map_tile($obj) == BAN_WATER) {
                            if ($obj->x->velocity < 0) {
                                if ($obj->x->velocity < -65536)
                                    $obj->x->velocity = -65536;
                                $obj->x->velocity += 1024;
                                if ($obj->x->velocity > 0)
                                    $obj->x->velocity = 0;
                            } else {
                                if ($obj->x->velocity > 65536)
                                    $obj->x->velocity = 65536;
                                $obj->x->velocity -= 1024;
                                if ($obj->x->velocity < 0)
                                    $obj->x->velocity = 0;
                            }
                            $obj->y->velocity += 1024;
                            if ($obj->y->velocity < -65536)
                                $obj->y->velocity = -65536;
                            if ($obj->y->velocity > 65536)
                                $obj->y->velocity = 65536;
                        }
                        $obj->x->pos += $obj->x->velocity;
                        if (($obj->y->pos >> 16) > 0 && ($this->map_tile($obj) == BAN_SOLID || $this->map_tile($obj) == BAN_ICE)) {
                            if ($obj->x->velocity < 0) {
                                $obj->x->pos = ((($obj->x->pos >> 16) + 16) & 0xfff0) << 16;
                                $obj->x->velocity = -$obj->x->velocity >> 2;
                            } else {
                                $obj->x->pos = (((($obj->x->pos >> 16) - 16) & 0xfff0) + 15) << 16;
                                $obj->x->velocity = -$obj->x->velocity >> 2;
                            }
                        }
                        $obj->y->pos += $obj->y->velocity;
                        if (($obj->x->pos >> 16) < -5 || ($obj->x->pos >> 16) > 405 || ($obj->y->pos >> 16) > 260)
                            $obj->used = false;
                        if (($obj->y->pos >> 16) > 0 && ($this->map_tile($obj) != BAN_VOID)) {
                            if ($obj->y->velocity < 0) {
                                if ($this->map_tile($obj) != BAN_WATER) {
                                    $obj->y->pos = ((($obj->y->pos >> 16) + 16) & 0xfff0) << 16;
                                    $obj->x->velocity >>= 2;
                                    $obj->y->velocity = -$obj->y->velocity >> 2;
                                }
                            } else {
                                if ($this->map_tile($obj) == BAN_SOLID) {
                                    if ($obj->y->velocity > 131072) {
                                        $obj->y->pos = (((($obj->y->pos >> 16) - 16) & 0xfff0) + 15) << 16;
                                        $obj->x->velocity >>= 2;
                                        $obj->y->velocity = -$obj->y->velocity >> 2;
                                    } else {
                                        if (rnd(100) < 10) {
                                            $s1 = rnd(4) - 2;
                                            $this->renderer->add_leftovers($obj->x->pos >> 16, ($obj->y->pos >> 16) + $s1, $this->img['objects'], object_gobs[$obj->frame]);
                                        }
                                        $obj->used = false;
                                    }
                                } else if ($this->map_tile($obj) == BAN_ICE) {
                                    $obj->y->pos = (((($obj->y->pos >> 16) - 16) & 0xfff0) + 15) << 16;
                                    if ($obj->y->velocity > 131072)
                                        $obj->y->velocity = -$obj->y->velocity >> 2;
                                    else
                                        $obj->y->velocity = 0;
                                }
                            }
                        }
                        if ($obj->x->velocity < 0 && $obj->x->velocity > -16384)
                            $obj->x->velocity = -16384;
                        if ($obj->x->velocity > 0 && $obj->x->velocity < 16384)
                            $obj->x->velocity = 16384;
                        if ($obj->used)
                            $this->renderer->add_pob($obj->x->pos >> 16, $obj->y->pos >> 16, $this->img['objects'], object_gobs[$obj->frame]);
                        break;
                }
            }
        }
    }

}
