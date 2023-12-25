console.log("Start", globalThis);

let _id = 1;
let boards = [];
let g_players = [];

function genId() { return String(_id++); }

class GPlayer {
  id;
  idx;
  board;
  constructor() {
    this.id = genId();
  }
}


class Board {
  players = [];
  current_level;

  constructor() {
    this.current_level = create_default_level();
    this.current_game = new Game_Session(this, this.current_level);
    this.current_game.start();
  }

  addPlayer(player) {
    this.players.push(player);
    player.board = this;
  }
}

/**
 *
 * @param {boolean} ai
 * @returns {string}
 */
function _start(ai) {
  let player = new GPlayer();
  let board = boards.find(b => b.players.length < 4);
  if (!board) boards.push(board = new Board());
  g_players.push(player);
  player.idx = board.players.length;
  board.addPlayer(player);
  return player.id;
}


// debug: run bots

document.addEventListener('DOMContentLoaded', () => {
  _start(true);
}, false);




// ** public api **

function start() {
  return _start(false);
}


function state(playerId) {
  let player = g_players.find(p => p.id == playerId);
  console.log('Player is alive', player);
  return [{x: 5, y: 6}, {x: 6, y: 7}];
}
