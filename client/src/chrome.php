<?php declare(strict_types=1);
use React\EventLoop\Loop;
use React\Promise\Promise;

function list_tabs() {
    $service_url = 'http://127.0.0.1:9222/json/list';
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curl_response = curl_exec($curl);
    if ($curl_response === false) {
        $info = curl_getinfo($curl);
        curl_close($curl);
        die('Error  getting tabs. ' . var_export($info));
    }
    curl_close($curl);
    $decoded = json_decode($curl_response);
    return $decoded;
}


$request_id = 0;
$conn;
$callbacks = array();


function runtime_evaluate($code) {
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
        // echo "send $msg\n";
        $conn->send($msg);
    });
}


function runtime_getProperties($object_id) {
    return new Promise(function(callable $callback) use ($object_id) {
        global $request_id, $conn, $callbacks;
        $request_id++;
        $callbacks[$request_id] = $callback;
        $msg = json_encode(array(
            'id' => $request_id,
            'method' => 'Runtime.getProperties',
            'params' => array('objectId' => $object_id),
        ));
        // echo "send $msg\n";
        $conn->send($msg);
    });
}
