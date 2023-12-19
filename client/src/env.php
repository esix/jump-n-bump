<?php
require_once __DIR__ . '/asset_data/animation_data.php';

const env = array(
    // 'JNB_MAX_PLAYERS' => 4,
    'MAX_OBJECTS' => 200,
    'animation_data' => new Animation_Data(),
    'level' => array(),
);

function rnd($max_value) {
    return rand(0, $max_value);
}


class Point {
    public float $pos;
    public float $velocity;
    public ?float $acceleration;

    public function __construct(float $pos, float $velocity, float $acceleration = null) {
        $this->pos = $pos;
        $this->velocity = $velocity;
        $this->acceleration = $acceleration;
    }
}

