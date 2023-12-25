function Game(movement, ai, animation, renderer, objects, key_pressed, level, is_server, rnd) {
  "use strict";
  var next_time = 0;
  var playing = false;
  reset_players();
  reset_level();

  function reset_players() {
    players = [
      new Player(0, [37, 39, 38], is_server, rnd),
      new Player(1, [65, 68, 87], is_server, rnd),
      new Player(2, [100, 102, 104], is_server, rnd),
      new Player(3, [74, 76, 73], is_server, rnd)
    ];
    players[3].ai = true;
  }

  function reset_level() {
    SET_BAN_MAP(level.ban_map);
    objects.reset_objects();

    for (var c1 = 0; c1 < env.JNB_MAX_PLAYERS; c1++) {
      if (players[c1].enabled) {
        players[c1].bumps = 0;
        for (var c2 = 0; c2 < env.JNB_MAX_PLAYERS; c2++) {
          players[c1].bumped[c2] = 0;
        }
        players[c1].position_player(c1);
      }
    }
  }

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
    for (var playerIndex = 0; playerIndex != players.length; ++playerIndex) {
      var p = players[playerIndex];
      if (p.enabled) {
        if (!p.dead_flag) {
          movement.steer_player(p);
        }
        p.update_player_animation();
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

  this.pause = function () {
    playing = false;
  }
}
