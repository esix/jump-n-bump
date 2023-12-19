<?php

require_once __DIR__ . "/../game/player.php";
require_once __DIR__ . "/../env.php";
require_once __DIR__ . "/../asset_data/rabbit_gobs.php";

const MAX = array(
    'POBS' => 200,
    'FLIES' => 20,
    'LEFTOVERS' => 50,
);



class Renderer {
    private $canvas;
    private $img;
    private $level;
    private $main;
    private $leftovers;

    public function __construct($canvas, $img, $level) {
        $this->canvas = $canvas;
        $this->img = $img;
        $this->level = $level;

        $this->main = array(
            'num_pobs' => 0,
            'pobs' => [],
        );
        $this->leftovers = array(
            'num_pobs' => 0,
            'pobs' =>  [],
        );
        $canvas_scale = 1;
    }

    public function add_leftovers($x, $y, $image, $gob) {
        $num_pobs = $this->leftovers['num_pobs'];
        $this->leftovers['pobs'][$num_pobs] = array( 'x' => $x, 'y' => $y, 'gob' => $gob, 'image' => $image );
        $this->leftovers['num_pobs']++;
    }

    public function add_pob($x, $y, $image, $gob) {
        if ($this->main['num_pobs'] >= MAX['POBS']) {
            return;
        }
        $this->main['pobs'][$this->main['num_pobs']] = array(
            'x' => $x,
            'y' => $y,
            'gob' => $gob,
            'image' => $image,
        );
        $this->main['num_pobs']++;
    }

    private function put_pob($x, $y, $gob, $img) {
        $sx = $gob['x'];
        $sy = $gob['y'];
        $sw = $gob['width'];
        $sh = $gob['height'];
        $hs_x = $gob['hotspot_x'];
        $hs_y = $gob['hotspot_y'];

        // ctx.drawImage(img, sx, sy, sw, sh, x - hs_x, y - hs_y, sw, sh);
        $scale = 3;
        $srcRect = new SDL_Rect($sx, $sy, $sw, $sh);
        $dstRect = new SDL_Rect($scale * ($x - $hs_x), $scale * ($y - $hs_y), $scale * $sw, $scale * $sh);

        SDL_RenderCopy($this->canvas, $img, $srcRect, $dstRect);
    }

    private function draw_pobs() {
        for ($c1 = $this->main['num_pobs'] - 1; $c1 >= 0; $c1--) {
            $pob = $this->main['pobs'][$c1];
            $this->put_pob($pob['x'], $pob['y'], $pob['gob'], $pob['image']);
        }
    }

    private function draw_leftovers() {
        for ($c1 = 0; $c1 != $this->leftovers['num_pobs']; ++$c1) {
            $pob = $this->leftovers['pobs'][$c1];
            $this->put_pob($pob['x'], $pob['y'], $pob['gob'], $pob['image']);
        }
    }

    private function resize_canvas() {
//         var x_scale = window.innerWidth / level.image.width;
//         var y_scale = window.innerHeight / level.image.height;
//         var new_scale = Math.floor(Math.min(x_scale, y_scale));
//
//         if (canvas_scale != new_scale) {
//             canvas_scale = new_scale;
//             canvas.width = 0;
//             canvas.height = 0;
//             canvas.width = level.image.width * canvas_scale;
//             canvas.height = level.image.height * canvas_scale;
//             ctx.scale(canvas_scale, canvas_scale);
//         }
    }

    public function draw() {
        global $players;
        $this->resize_canvas();

        SDL_RenderCopy($this->canvas, $this->level['image'], null, null);

        foreach ($players as $p) {
            if ($p->enabled) {
                $this->add_pob($p->x->pos >> 16, $p->y->pos >> 16, $this->img['rabbits'], rabbit_gobs[$p->get_image() + $p->player_index * 18]);
            }
        }
        $this->draw_leftovers();
        $this->draw_pobs();

        SDL_RenderCopy($this->canvas, $this->level['mask'], null, null);
        $this->main['num_pobs'] = 0;

        SDL_RenderPresent($this->canvas);
    }
}
