let g_boards = [];
let g_players = [];




class Board {
  current_level = null;
  current_game = null;

  constructor() {
    this.current_level = create_default_level();
    this.current_game = new Game_Session(this, this.current_level);
    this.current_game.start();
  }

  addPlayer() {
    let p = this.current_game.add_player();
    if (p) {
      p.board = this;
    }
    return p;
  }
}



document.addEventListener('DOMContentLoaded', () => {
  // debug: run players
  start();
  start();
}, false);




// ** public api **

function start() {
  let player = null, board = null;
  for (board of g_boards) {
    if ((player = board.addPlayer())) break;
  }
  if (!player) {
    g_boards.push(board = new Board());
    player = board.addPlayer();
  }
  g_players.push(player);
  return player.id;
}


function state(playerId) {
  let player = g_players.find(p => p.id == playerId);
  console.log('Player is alive', player);
  return [{x: 5, y: 6}, {x: 6, y: 7}];
}
