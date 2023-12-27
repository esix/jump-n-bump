<?php declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/asset_data/default_levelmap.php';
require_once __DIR__ . '/src/interaction/game_session.php';
require_once __DIR__ . '/src/chrome.php';
use React\EventLoop\Loop;
use React\Promise\Promise;

error_reporting(E_ALL);

const WINDOW_WIDTH = 1200;
const WINDOW_HEIGHT = 768;

// start

$tabs = list_tabs();
foreach ($tabs as $tab) {
    if (str_ends_with($tab->url, 'jnb.html')) {
        $webSocketDebuggerUrl = $tab->webSocketDebuggerUrl;             // TODO: search by title  "title": "Jump &#39;n Bump server",
    }
}

if (!$webSocketDebuggerUrl) {
    var_dump(tabs);
    die ('Tab not found');
}


$current_game;


\Ratchet\Client\connect($webSocketDebuggerUrl)
    ->then(function($connection) {
        global $conn;
        $conn = $connection;
        echo "Connected\n";
        $conn->on('message', function($msg) use ($conn) {
            global $callbacks;
            $decoded = json_decode("$msg");
            if ($decoded->id && $decoded->result && ($callbacks[$decoded->id] ?? null)) {
                call_user_func($callbacks[$decoded->id], $decoded->result);
            } else {
                echo "Received: {$msg}\n";
            }
        });
        // $conn->close();

    }, function ($e) {
        echo "Could not connect: {$e->getMessage()}\n";
        exit(1);
    })
    ->then(function() {
        // return runtime_evaluate('start()');
        return runtime_evaluate('window.location.href');
    })
    ->then(function($href) {
        var_dump($href->result->value);
    })
    ->then(function() {
        // return runtime_evaluate('start()');
        return runtime_evaluate('window.start()');
    })
    ->then(function($start_state) {
        global $player_id;
        $player_id = $start_state->result->value;
        if (!$player_id) {
            var_dump($start_state);
            die ("No player id\n");
        }
        echo "player_id = $player_id\n";
    })
    ->then(function() {
        global $window, $canvas, $current_game, $event;

        SDL_Init(SDL_INIT_VIDEO);
        $window = SDL_CreateWindow("Jump 'n Bump", SDL_WINDOWPOS_UNDEFINED, SDL_WINDOWPOS_UNDEFINED, WINDOW_WIDTH, WINDOW_HEIGHT, SDL_WINDOW_SHOWN);
        $canvas = SDL_CreateRenderer($window, -1, SDL_RENDERER_ACCELERATED | SDL_RENDERER_TARGETTEXTURE);

        $current_level = create_default_level($canvas);
        $current_game = new Game_Session($canvas, $current_level);
        $current_game->start();

        // Main loop
        $event = new SDL_Event;

        // 1000 / FPS
        Loop::addPeriodicTimer(0.005, function () {
            global $canvas, $window;
            if (!game_loop()) {
                SDL_DestroyRenderer($canvas);
                SDL_DestroyWindow($window);
                SDL_Quit();
                die(1);
            }
        });

        Loop::addTimer(0.01, function() { request_state(); });
    });


function parse_player_info($player_info) {
    $result = array();

    foreach ($player_info as $obj) {
        if ($obj->isOwn) {
            $name = $obj->name;
            $value = $obj->value ? $obj->value->value : null;
            $result[$obj->name] = $value;
        }
    }

    return $result;
}


function request_state() {
    global $players, $player_id;

    foreach($players as $p) {
        if ($p->id === $player_id) {
            $xPos = $p->x->pos;
            $xVelocity = $p->x->velocity;
            $yPos = $p->y->pos;
            $yVelocity = $p->y->velocity;
            runtime_evaluate("window.setPlayerInfo('$player_id', $xPos, $yPos, $xVelocity, $yVelocity)");
        }
    }

    runtime_evaluate("window.state('$player_id')")->then(function($state) {
        $object_id = $state->result->objectId;
        return runtime_getProperties($object_id);

    })
    ->then(function($response) {
        $result = $response->result;
        $promises = array();

        foreach ($result as $obj) {
            if (is_numeric($obj->name)) {
                $promises[$obj->name] = runtime_getProperties($obj->value->objectId);
            }
        }

        return React\Promise\all($promises);
    })
    ->then(function($obj_players) {
        global $current_game;
        $players_info = array();
        foreach ($obj_players as $player_idx => $obj) {
            $player_info = parse_player_info($obj->result);
            $players_info[$player_idx] = $player_info;
        }
        $current_game->set_player_info($players_info);
    })
    ->then(function() {
        Loop::addTimer(0.005, function() { request_state(); });
    });
}


function game_loop() {
    global $event, $current_game;

    // while (true) {
    if (SDL_PollEvent($event) && $event->type == SDL_QUIT) {
        return false;
    }

    $keyboardState = SDL_GetKeyboardState($numkeys);
    $current_game->pump($keyboardState);
    // SDL_Delay(5);
    // }
    return true;
}

