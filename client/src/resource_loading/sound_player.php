<?php

class Sound_Player {
    private $muted;
    private $sounds;
    private $sfx_extension;

    public function __construct($muted) {
        $this->muted = $muted;
        $this->sounds = [];
    }

    public function set_single_audio_muted($audio, $val) {
        if ($val) $audio->pause();
        else if ($audio->loop) $audio->play();
    }

    public function set_muted($val) {
        $this->muted = $val;
        foreach ($this->sounds as $sound) {
            $this->set_single_audio_muted($sound['audio'], val);
        }
    }

    public function toggle_sound() {
        $this->set_muted(!$this->muted);
    }

    public function play_sound($sfx_name, $loop) {
//         var i, replace = -1;
//         var audio;
//         for (i in sounds) {
//             if (sounds[i].audio.ended) {
//                 replace = i;
//                 if (sounds[i].sfx_name == sfx_name) {
//                     audio = sounds[i].audio;
//                     if (!muted) {
//                         audio.play();
//                     }
//                     return;
//                 }
//             }
//         }
//         audio = document.createElement('audio');
//         sfx_extension = audio.canPlayType('audio/mpeg')?'mp3':'ogg';
//         const onPlayThrough = function(ev) {
//             this.removeEventListener('canplaythrough', onPlayThrough, false);
//
//             if (!muted) {
//                 this.play();
//             }
//         };
//         audio.addEventListener('canplaythrough', onPlayThrough, false);
//         audio.src = "sound/" + sfx_name + "." + sfx_extension;
//         if (loop) audio.loop = true;
//         audio.load();
//         for (var i in sounds) {
//             if (sounds[i].ended) {
//                 sounds[i] = audio;
//                 audio = null;
//                 break;
//             }
//         }
//         sounds.push({ audio: audio, sfx_name: sfx_name });
    }
}
