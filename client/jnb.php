<?php declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/asset_data/default_levelmap.php';
require_once __DIR__ . '/src/interaction/game_session.php';
require_once __DIR__ . '/src/chrome-remote-interface/index.php';
use React\EventLoop\Loop;
use React\Promise\Promise;

error_reporting(E_ALL);

const WINDOW_WIDTH = 1200;
const WINDOW_HEIGHT = 768;

// start

$tabs = list_tabs();
$webSocketDebuggerUrl = $tabs[0]->webSocketDebuggerUrl;
$request_id = 0;
$conn;
$player_id;
$callbacks = array();


function run_code($code) {
    return new Promise(function(callable $callback) use ($code) {
        global $request_id, $conn, $callbacks;
        $request_id++;
        $callbacks[$request_id] = $callback;
        $msg = json_encode(array(
            'id' => $request_id,
            'method' => 'Runtime.evaluate',
            // 'sessionId' => $sessionId,
            'params' => array('expression' => $code),
        ));
        echo "send $msg\n";
        $conn->send($msg);
    });
}


\Ratchet\Client\connect($webSocketDebuggerUrl)
    ->then(function($connection) {
        global $conn;
        $conn = $connection;
        echo "Connected\n";
        $conn->on('message', function($msg) use ($conn) {
            global $callbacks;
            $decoded = json_decode("$msg");
            if ($callbacks[$decoded->id] ?? null) {
                call_user_func($callbacks[$decoded->id], $decoded->result);
            } else {
                echo "Received: {$msg}\n";
            }
        });
        // $conn->close();

    }, function ($e) {
        echo "Could not connect: {$e->getMessage()}\n";
    })
    ->then(function() {
        echo "AAAAAAA\n";
        return run_code('start()');
    })
    ->then(function($start_state) {
        echo "start returned:\n";
        var_dump($start_state);
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

        Loop::addPeriodicTimer(0.005, function () {
            global $canvas, $window;
            if (!game_loop()) {
                SDL_DestroyRenderer($canvas);
                SDL_DestroyWindow($window);
                SDL_Quit();
                die(1);
            }
        });
    });



function game_loop() {
    global $event, $current_game;

    // while (true) {
    if (SDL_PollEvent($event) && $event->type == SDL_QUIT) {
        return false;
    }

    $keyboardState = SDL_GetKeyboardState($numkeys);
    $current_game->set_current_keys($keyboardState);
    $current_game->pump();
    // SDL_Delay(5);
    // }
    return true;
}


function request_state() {
    global $player_id;
    return run_code("state('$player_id')")->then(function($state) {
        echo "request_state:\n";
        var_dump($state);
    });
}

Loop::addPeriodicTimer(5, function () {
    echo "PeriodicTimer\n";
    request_state();
});
