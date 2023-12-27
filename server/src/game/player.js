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
  this.board = null;
  this.action_left = false;
  this.action_up = false;
  this.action_right = false;
  this.dead_flag = false;
  this.bumps = 0;
  this.x = { pos: 0, velocity: 0 };
  this.y = { pos: 0, velocity: 0 };
  this.direction = 0;
  this.jump_ready = false;
  this.jump_abort = false;
  this.in_water = false;
  this.anim = 0;
  this.frame = 0;
  this.frame_tick = 0;
  this.lastTimer = new Date().valueOf();

  this.setConnected = function() {
    this.lastTimer = new Date().valueOf();
  }

  this.isDisconnected = function() {
    return (new Date().valueOf() - this.lastTimer > 5000);                  // 5 seconds no signal
  }

  this.set_anim = function (animIndex) {
    this.anim = animIndex;
    this.frame = 0;
    this.frame_tick = 0;
  };

  this.get_image = function () { return env.animation_data.players[this.anim].frame[this.frame].image + this.direction * 9; };

  this.toJSON = () => {
    return {
      id: this.id,
      idx: this.idx,
      enabled: this.enabled,
      bumps: this.bumps,
      xPos: this.x.pos,
      xVelocity: this.x.velocity,
      yPos: this.y.pos,
      yVelocity: this.y.velocity,
      direction: this.direction,
    };
  }
}
