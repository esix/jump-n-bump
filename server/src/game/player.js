/**
 *
 * @param {string} id
 * @param {number} idx
 * @param {number[]} keys
 * @param {boolean} enabled
 * @param {boolean} ai
 * @constructor
 */
function Player(id, idx, keys, enabled, ai) {
  "use strict";
  this.id = id;
  this.idx = idx;
  this.keys = keys
  this.enabled = enabled;
  this.ai = ai;
  this.action_left = false;
  this.action_up = false;
  this.action_right = false;
  this.dead_flag = false;
  this.bumps = 0;
  this.x = { pos: 0 };
  this.y = { pos: 0 };
  this.x.velocity = 0;
  this.y.velocity = 0;
  this.direction = 0;
  this.jump_ready = false;
  this.jump_abort = false;
  this.in_water = false;
  this.anim = 0;
  this.frame = 0;
  this.frame_tick = 0;

  this.set_anim = function (animIndex) {
    this.anim = animIndex;
    this.frame = 0;
    this.frame_tick = 0;
  };

  this.get_image = function () { return env.animation_data.players[this.anim].frame[this.frame].image + this.direction * 9; };
}
