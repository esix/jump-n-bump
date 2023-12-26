/**
 *
 * @type {Board[]}
 */
let g_boards = [];

/**
 *
 * @type {Player[]}
 */
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

  getPlayers() {
    return this.current_game.players;
  }
}



document.addEventListener('DOMContentLoaded', () => {
  // debug: run players
  // start();
  // start();
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

/**
 *
 * @param {string} playerId
 * @returns {*[]}
 */
function state(playerId) {
  let player = g_players.find(p => p.id == playerId);
  // console.log('Player is alive', player);
  if (player) {
    return player.board.getPlayers().map(p => p.toJSON());
  } else {
    return [];
  }
}


function setPlayerInfo(playerId, xPos, yPos, xVelocity, yVelocity) {
  let player = g_players.find(p => p.id == playerId);
  if (player) {
    player.x.pos = xPos;
    player.y.pos = yPos;
    player.x.velocity = xVelocity;
    player.y.velocity = yVelocity;
  }
}
