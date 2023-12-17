<?php

require_once __DIR__ . '/chrome.php';


function CDP($options = null) {
    $notifier = new Peridot\EventEmitter();
    $deferred = new React\Promise\Deferred();
    $chrome = null;

    $notifier->once('connect', function () use ($deferred, $chrome) {
        var_dump($chrome);
    });
    $notifier->once('error', function () use ($deferred) {
        $deferred->reject(new Exception('Exception message'));
    });

    $chrome = new Chrome($options, $notifier);
    return $deferred->promise();
}
