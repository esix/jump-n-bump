
function Keyboard() {
  "use strict";
  var self = this;
  var keys_pressed = {}
  this.key_pressed = function(key) {
    return keys_pressed[key];
  }

  this.addkey = function(player, k) {
    keys_pressed[player.keys[k]] = true;
  }

  this.delkey = function(player, k) {
    keys_pressed[player.keys[k]] = false;
  }

  this.onKeyDown = function(evt) {
    keys_pressed[evt.keyCode] = true;
  }

  this.onKeyUp = function(evt) {
    keys_pressed[evt.keyCode] = false;
  }

  // function toggle_player_enabled(idx) {
  //   players[idx].enabled = !players[idx].enabled;
  // }
  //
  // function toggle_ai_enabled(idx) {
  //   var p = players[idx];
  //   p.ai = !p.ai;
  //   self.delkey(p, 0);
  //   self.delkey(p, 1);
  //   self.delkey(p, 2);
  // }
}
