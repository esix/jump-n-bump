<?php

class devtools {

    private static function devToolsInterface($path, $options) {
        $transport = $options['secure'] ? 'https' : 'http';
        $requestOptions = array(
            'method' => $options['method'],
            'host' => $options['host'] ?? defaults['HOST'],
            'port' => $options['port'] ?? defaults['PORT'],
            'useHostName' => $options['useHostName'],
            'path' => ($options['alterPath'] ? $options['alterPath']($path) : $path),
        );
        externalRequest($transport, $requestOptions, $callback);
    }

    public static function List() {
        return $self::devToolsInterface('/json/list', $options)
            ->then(function($result) { return json_decode($result); });
    }
}
