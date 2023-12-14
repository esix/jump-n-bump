<?php

require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/../game/level.php";
require_once __DIR__ . "/../asset_data/default_levelmap.php";



class Obj {
    public $used = false;
    public $type;
    public $x;
    public $y;
    public $anim;
    public $frame;
    public $ticks;
    public $image;
}


class Objects {
    public $objects = [];

    const SPRING = 0;
    const SPLASH = 1;
    const SMOKE = 2;
    const YEL_BUTFLY = 3;
    const PINK_BUTFLY = 4;
    const FUR = 5;
    const FLESH = 6;
    const FLESH_TRACE = 7;

    const ANIM_SPRING = 0;
    const ANIM_SPLASH = 1;
    const ANIM_SMOKE = 2;
    const ANIM_YEL_BUTFLY_RIGHT = 3;
    const ANIM_YEL_BUTFLY_LEFT = 4;
    const ANIM_PINK_BUTFLY_RIGHT = 5;
    const ANIM_PINK_BUTFLY_LEFT = 6;
    const ANIM_FLESH_TRACE = 7;

    public function __construct() {
        //
    }

    public function add($type, $x, $y, $x_add, $y_add, $anim, $frame) {
        for ($c1 = 0; $c1 < env['MAX_OBJECTS']; $c1++) {
            if (!$this->objects[$c1]->used) {
                $obj = new Obj();
                $obj->used = true;
                $obj->type = $type;
                $obj->x = new Point($x << 16, $x_add, 0);
                $obj->y = new Point($y << 16, $y_add, 0);
                $obj->anim = $anim;
                $obj->frame = $frame;
                $this->objects[$c1] = $obj;
                if ($frame < env['animation_data']->objects[$anim]['num_frames']) {
                    $this->objects[$c1]->ticks = env['animation_data']->objects[$anim]['frame'][$frame]['ticks'];
                    $this->objects[$c1]->image = env['animation_data']->objects[$anim]['frame'][$frame]['image'];
                }
                break;
            }
        }
    }

    public function add_gore($x, $y, $c2) {
        for ($c4 = 0; $c4 < 6; $c4++)
            $this->add(self::FUR, ($x >> 16) + 6 + rnd(5), ($y >> 16) + 6 + rnd(5), (rnd(65535) - 32768) * 3, (rnd(65535) - 32768) * 3, 0, 44 + $c2 * 8);
        for ($c4 = 0; $c4 < 6; $c4++)
            $this->add(self::FLESH, ($x >> 16) + 6 + rnd(5), ($y >> 16) + 6 + rnd(5), (rnd(65535) - 32768) * 3, (rnd(65535) - 32768) * 3, 0, 76);
        for ($c4 = 0; $c4 < 6; $c4++)
            $this->add(self::FLESH, ($x >> 16) + 6 + rnd(5), ($y >> 16) + 6 + rnd(5), (rnd(65535) - 32768) * 3, (rnd(65535) - 32768) * 3, 0, 77);
        for ($c4 = 0; $c4 < 8; $c4++)
            $this->add(self::FLESH, ($x >> 16) + 6 + rnd(5), ($y >> 16) + 6 + rnd(5), (rnd(65535) - 32768) * 3, (rnd(65535) - 32768) * 3, 0, 78);
        for ($c4 = 0; $c4 < 10; $c4++)
            $this->add(self::FLESH, ($x >> 16) + 6 + rnd(5), ($y >> 16) + 6 + rnd(5), (rnd(65535) - 32768) * 3, (rnd(65535) - 32768) * 3, 0, 79);
    }

    private function create_butterfly($obj) {
        while (1) {
            $x = rnd(LEVEL_WIDTH);
            $y = rnd(LEVEL_HEIGHT);
            if (GET_BAN_MAP($x, $y) == BAN_VOID) {
                $this->add($obj, ($x << LEVEL_SCALE_FACTOR) + 8, ($y << LEVEL_SCALE_FACTOR) + 8, (rnd(65535) - 32768) * 2, (rnd(65535) - 32768) * 2, 0, 0);
                break;
            }
        }
    }

    public function reset_objects() {
        $this->objects = [];
        for ($i = 0; $i < env['MAX_OBJECTS']; $i++) {
            $this->objects[$i] = new Obj();
        }
        for ($y = 0; $y < LEVEL_HEIGHT; $y++) {
            for ($x = 0; $x < LEVEL_WIDTH; $x++) {
                if (GET_BAN_MAP($x, $y) == BAN_SPRING) {
                    $this->add(self::SPRING, $x << LEVEL_SCALE_FACTOR, $y << LEVEL_SCALE_FACTOR, 0, 0, self::ANIM_SPRING, 5);
                }
            }
        }
        $this->create_butterfly(self::YEL_BUTFLY);
        $this->create_butterfly(self::YEL_BUTFLY);
        $this->create_butterfly(self::PINK_BUTFLY);
        $this->create_butterfly(self::PINK_BUTFLY);
        return $this->objects;
    }
}
