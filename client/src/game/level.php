<?php

require_once __DIR__ . "/../asset_data/default_levelmap.php";

const SQUARE_SIZE = 1 << LEVEL_SCALE_FACTOR;
const BAN_VOID	= 0;
const BAN_SOLID	= 1;
const BAN_WATER	= 2;
const BAN_ICE	= 3;
const BAN_SPRING = 4;

$ban_map;

function SET_BAN_MAP($new_ban_map) {
    global $ban_map;
    $ban_map = $new_ban_map;
}

function GET_BAN_MAP_XY($x, $y) {
    global $ban_map;
    if ($y < 0) $y = 0;
    return $ban_map[($x >> LEVEL_SCALE_FACTOR) + ($y >> LEVEL_SCALE_FACTOR) * LEVEL_WIDTH];
}

function GET_BAN_MAP($x, $y) {
    global $ban_map;
    if ($y < 0) $y = 0;
    return $ban_map[$x + $y * LEVEL_WIDTH] ?? null;
}

function GET_BAN_MAP_IN_WATER($s1, $s2) {
    return (GET_BAN_MAP_XY(($s1), (($s2) + 7)) == BAN_VOID || GET_BAN_MAP_XY((($s1) + 15), (($s2) + 7)) == BAN_VOID)
	&& (GET_BAN_MAP_XY(($s1), (($s2) + 8)) == BAN_WATER || GET_BAN_MAP_XY((($s1) + 15), (($s2) + 8)) == BAN_WATER);
}
