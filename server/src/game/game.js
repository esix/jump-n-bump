
function position_player(player, players) {
  var c1;
  var s1, s2;

  while (1) {
    while (1) {
      s1 = rnd(LEVEL_WIDTH);
      s2 = rnd(LEVEL_HEIGHT);
      if (GET_BAN_MAP(s1, s2) == BAN_VOID && (GET_BAN_MAP(s1, s2 + 1) == BAN_SOLID || GET_BAN_MAP(s1, s2 + 1) == BAN_ICE))
        break;
    }
    for (c1 = 0; c1 < env.JNB_MAX_PLAYERS; c1++) {
      if (players[c1] !== player && players[c1].enabled) {
        if (Math.abs((s1 << LEVEL_SCALE_FACTOR) - (players[c1].x.pos >> 16)) < 32 && Math.abs((s2 << LEVEL_SCALE_FACTOR) - (players[c1].y.pos >> 16)) < 32)
          break;
      }
    }
    if (c1 == env.JNB_MAX_PLAYERS) {
      player.x.pos = s1 << 20;
      player.y.pos = s2 << 20;
      player.x.velocity = player.y.velocity = 0;
      player.direction = 0;
      player.jump_ready = 1;
      player.in_water = 0;
      player.set_anim(0);
      player.dead_flag = 0;
      break;
    }
  }
}


function update_player_animation(player, players) {
  player.frame_tick++;
  if (player.frame_tick >= env.animation_data.players[player.anim].frame[player.frame].ticks) {
    player.frame++;
    if (player.frame >= env.animation_data.players[player.anim].num_frames) {
      if (player.anim != 6)
        player.frame = env.animation_data.players[player.anim].restart_frame;
      else
        position_player(player, players);
    }
    player.frame_tick = 0;
  }
}



function Game(players, movement, ai, animation, renderer, objects, key_pressed, level) {
  "use strict";
  var next_time = 0;
  var playing = false;
  SET_BAN_MAP(level.ban_map);
  objects.reset_objects();

  function timeGetTime() {
    return new Date().getTime();
  }

  function update_player_actions() {
    for (var i = 0; i != players.length; ++i) {
      players[i].action_left = key_pressed(players[i].keys[0]);
      players[i].action_right = key_pressed(players[i].keys[1]);
      players[i].action_up = key_pressed(players[i].keys[2]);
    }
  }

  function steer_players() {
    ai.cpu_move();
    update_player_actions();
    for (var idx = 0; idx != players.length; ++idx) {
      var p = players[idx];
      if (p.enabled) {
        if (!p.dead_flag) {
          movement.steer_player(p);
        }
        update_player_animation(p, players);
      }
    }
  }

  function game_iteration() {
    steer_players();
    movement.collision_check();
    animation.update_object();
    renderer.draw();
  }

  function pump() {
    while (playing) {
      game_iteration();
      var now = timeGetTime();
      var time_diff = next_time - now;
      next_time += (1000 / 60);

      if (time_diff > 0) {
        // we have time left
        setTimeout(pump, time_diff);
        break;
      }
    }
  }

  this.start = function () {
    next_time = timeGetTime() + 1000;
    playing = true;
    pump();
  }
}
