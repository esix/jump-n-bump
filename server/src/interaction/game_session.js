let _id = 1;
function genId() { return String(_id++); }


function Game_Session(board, level) {
  "use strict";
  var canvas = document.getElementById('screen');
  var img = {
    rabbits: document.getElementById('rabbits'),
    objects: document.getElementById('objects'),
    numbers: document.getElementById('numbers')
  };

  this.players = [
    new Player('', 0, [37, 39, 38], false, false),
    new Player('', 1, [65, 68, 87], false, false),
    new Player('', 2, [100, 102, 104], false, false),
    new Player('', 3, [74, 76, 73], true, true)
  ];


  var renderer = new Renderer(this.players, canvas, img, level);
  var objects = new Objects();
  var keyboard = new Keyboard();
  var ai = new AI(this.players, keyboard);
  var animation = new Animation(renderer, img, objects);
  var movement = new Movement(this.players, renderer, img, objects);
  var game = new Game(this.players, movement, ai, animation, renderer, objects, keyboard.key_pressed, level);

  position_player(this.players[3], this.players);

  this.start = function () {
    game.start();
  }

  this.add_player = () => {
    for (let idx = 0; idx < env.JNB_MAX_PLAYERS; idx++) {
      let p = this.players[idx];
      if (!p.enabled) {
        p.enabled = true;
        p.id = genId();
        p.bumps = 0;
        position_player(p, this.players);
        return p;
      }
    }
    return null;
  }

  document.onkeydown = keyboard.onKeyDown;
  document.onkeyup = keyboard.onKeyUp;
}

