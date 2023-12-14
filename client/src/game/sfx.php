<?php

class Sfx {
    private $sound_player;

    public function __construct($sound_player) {
        $this->sound_player = $sound_player;
    }

    public function jump() { $this->sound_player->play_sound("jump", false); }
    public function land() { $this->sound_player->play_sound("land", false); }
    public function death() { $this->sound_player->play_sound("death", false); }
    public function spring() { $this->sound_player->play_sound("spring", false); }
    public function splash() { $this->sound_player->play_sound("splash", false); }
    public function fly() { $this->sound_player->play_sound("fly", false); }

    public function music() {
        $this->sound_player->play_sound("bump", true);
    }
}
