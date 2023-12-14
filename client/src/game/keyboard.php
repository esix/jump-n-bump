<?php

require_once __DIR__ . "/../game/player.php";

class Keyboard {
    private $keys_pressed;

    public function __construct($key_function_mappings) {
        $this->keys_pressed = array();
    }

    public function key_pressed($key) {
        return $this->keys_pressed[$key];
    }

    public function addkey($player, $k) {
        $this->keys_pressed[$player->keys[$k]] = true;
    }

    public function delkey($player, $k) {
        $this->keys_pressed[$player->keys[$k]] = false;
    }

    public function set_current_keys($current_keys) {
        $this->keys_pressed = $current_keys;
    }


//     public function onKeyDown($keyCode) {
//         $this->keys_pressed[$keyCode] = true;
//     }

    public function onKeyUp($evt) {
//         keys_pressed[evt.keyCode] = false;
//         var uppercase_string = String.fromCharCode(evt.keyCode);
//         if (evt.keyCode >= 49 && evt.keyCode <= 52) {
//             var i = evt.keyCode - 49;
//             if (evt.altKey) toggle_ai_enabled(i);
//             else toggle_player_enabled(i);
//         } else {
//             var action = key_function_mappings[uppercase_string];
//             if (action != null) action();
//         }
    }

    private function toggle_player_enabled($playerIndex) {
        global $player;
        $player[$playerIndex]->enabled = !$player[$playerIndex]->enabled;
    }

    private function toggle_ai_enabled($playerIndex) {
        global $player;
        $p = $player[$playerIndex];
        $p->ai = !$p->ai;
        $this->delkey($p, 0);
        $this->delkey($p, 1);
        $this->delkey($p, 2);
    }
}
