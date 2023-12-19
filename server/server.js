console.log("Start", globalThis);

let _id = 1;
let boards = [];
let players = [];

function genId() { return String(_id++); }

class Player {
  id;
  idx;
  board;
  constructor() {
    this.id = genId();
  }
}


class Board {
  players = [];

  addPlayer(player) {
    this.players.push(player);
    player.board = this;
  }
}


function start() {
  let player = new Player();
  let board = boards.find(b => b.players.length < 4);
  if (!board) boards.push(board = new Board());
  players.push(player);
  player.idx = board.players.length;
  board.addPlayer(player);
  return player.id;
}


function state(playerId) {
  let player = players.find(p => p.id == playerId);
  console.log('Player is alive', player);
  return [{x: 5, y: 6}, {x: 6, y: 7}];
}

