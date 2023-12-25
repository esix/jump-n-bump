<?php

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



$_nextCommandId = 1;

function _enqueueCommand($method, $params, $sessionId, $callback) {
    global $_nextCommandId;
    $id = $_nextCommandId++;
    $message = array(
        'id' => $id,
        'method' => $method,
        // 'sessionId' => $sessionId,
        'params' => $params ?? array(),
    );
//     this._ws.send(JSON.stringify(message), (err) => {
//         if (err) {
//             // handle low-level WebSocket errors
//             if (typeof callback === 'function') {
//                 callback(err);
//             }
//         } else {
//             this._callbacks[id] = callback;
//         }
//     });
}
