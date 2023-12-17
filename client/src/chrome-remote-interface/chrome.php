<?php
require_once __DIR__ . '/devtools.php';

const chrome_defaults = array(
    'HOST' => 'localhost',
    'PORT' => 9222
);


class Chrome extends Peridot\EventEmitter {
    private $host;
    private $port;
    private $secure;
    private $useHostName;
    private $alterPath;
    private $protocol;
    private $local;
    private $target;
    private $_notifier;
    private $_callbacks;
    private $_nextCommandId;
    private $webSocketUrl;

    public function __construct($options, $notifier) {
        // options
        $defaultTarget = function ($targets) {
            // prefer type = 'page' inspectable targets as they represents
            // browser tabs (fall back to the first inspectable target
            // otherwise)
            $backup;
            $target = $targets.find(function ($target) {
                if ($target->webSocketDebuggerUrl) {
                    $backup = $backup ?? $target;
                    return $target->type === 'page';
                } else {
                    return false;
                }
            });
            $target = $target ?? $backup;
            if ($target) {
                return $target;
            } else {
                throw new Exception('No inspectable targets');
            }
        };
        $options = $options ?? array();
        $this->host = $options['host'] ?? chrome_defaults['HOST'];
        $this->port = $options['port'] ?? chrome_defaults['PORT'];
        $this->secure = !!($options['secure'] ?? false);
        $this->useHostName = !!($options['useHostName'] ?? false);
        $this->alterPath = $options['alterPath'] ?? (function ($path) { return $path; });
        $this->protocol = $options['protocol'] ?? null;
        $this->local = !!($options['local'] ?? false);
        $this->target = $options['target'] ?? $defaultTarget;
        // locals
        $this->_notifier = $notifier;
        $this->_callbacks = array();
        $this->_nextCommandId = 1;
        // properties
        $this->webSocketUrl = null;
        // operations
        $this->_start();
    }

    // avoid misinterpreting protocol's members as custom util.inspect functions
    public function inspect($depth, $options) {
//         options.customInspect = false;
//         return util.inspect(this, options);
    }

    public function send($method, $params, $sessionId, $callback) {
//         // handle optional arguments
//         const optionals = Array.from(arguments).slice(1);
//         params = optionals.find(x => typeof x === 'object');
//         sessionId = optionals.find(x => typeof x === 'string');
//         callback = optionals.find(x => typeof x === 'function');
//         // return a promise when a callback is not provided
//         if (typeof callback === 'function') {
//             this._enqueueCommand(method, params, sessionId, callback);
//             return undefined;
//         } else {
//             return new Promise((fulfill, reject) => {
//                 this._enqueueCommand(method, params, sessionId, (error, response) => {
//                     if (error) {
//                         const request = {method, params, sessionId};
//                         reject(
//                             error instanceof Error
//                                 ? error // low-level WebSocket error
//                                 : new ProtocolError(request, response)
//                         );
//                     } else {
//                         fulfill(response);
//                     }
//                 });
//             });
//         }
    }

    public function close($callback) {
//         const closeWebSocket = (callback) => {
//             // don't close if it's already closed
//             if (this._ws.readyState === 3) {
//                 callback();
//             } else {
//                 // don't notify on user-initiated shutdown ('disconnect' event)
//                 this._ws.removeAllListeners('close');
//                 this._ws.once('close', () => {
//                     this._ws.removeAllListeners();
//                     this._handleConnectionClose();
//                     callback();
//                 });
//                 this._ws.close();
//             }
//         };
//         if (typeof callback === 'function') {
//             closeWebSocket(callback);
//             return undefined;
//         } else {
//             return new Promise((fulfill, reject) => {
//                 closeWebSocket(fulfill);
//             });
//         }
    }

    // initiate the connection process
    // async
    public function _start() {
        $options = array(
            'host' => $this->host,
            'port' => $this->port,
            'secure' => $this->secure,
            'useHostName' => $this->useHostName,
            'alterPath' => $this->alterPath
        );
        $this->_fetchDebuggerURL($options)
            ->then(function ($url) {  var_dump($url);    })
            ->catch(function ($err) {  $this->_notifier->emit('error', $err);  });

//         try {
            // fetch the WebSocket debugger URL
//             const url = await this._fetchDebuggerURL(options);
            // allow the user to alter the URL
//             const urlObject = parseUrl(url);
//             urlObject.pathname = options.alterPath(urlObject.pathname);
//             this.webSocketUrl = formatUrl(urlObject);
            // update the connection parameters using the debugging URL
//             options.host = urlObject.hostname;
//             options.port = urlObject.port || options.port;
            // fetch the protocol and prepare the API
//             const protocol = await this._fetchProtocol(options);
//             api.prepare(this, protocol);
            // finally connect to the WebSocket
//             await this._connectToWebSocket();
            // since the handler is executed synchronously, the emit() must be
            // performed in the next tick so that uncaught errors in the client code
            // are not intercepted by the Promise mechanism and therefore reported
            // via the 'error' event
//             process.nextTick(() => {
//                 this._notifier.emit('connect', this);
//             });
//         } catch (Exception $err) {
//             $this->_notifier->emit('error', $err);
//         }
    }

    // fetch the WebSocket URL according to 'target'
    // async
    public function _fetchDebuggerURL($options) {
        $userTarget = $this->target;
//         switch (gettype($userTarget)) {
//         case 'string': {
//             let idOrUrl = userTarget;
//             // use default host and port if omitted (and a relative URL is specified)
//             if (idOrUrl.startsWith('/')) {
//                 idOrUrl = `ws://${this.host}:${this.port}${idOrUrl}`;
//             }
//             // a WebSocket URL is specified by the user (e.g., node-inspector)
//             if (idOrUrl.match(/^wss?:/i)) {
//                 return idOrUrl; // done!
//             }
//             // a target id is specified by the user
//             else {
//                 const targets = await devtools.List(options);
//                 const object = targets.find((target) => target.id === idOrUrl);
//                 return object.webSocketDebuggerUrl;
//             }
//         }
//         case 'array': {
//             const $object = $userTarget;
//             return $object['webSocketDebuggerUrl'];
//         }
        if (is_callable($userTarget)) {
            $func = $userTarget;
            return devtools::List($options)
                    ->then(function ($target) {
                        $result = $func($targets);
                        $object = is_numeric($result) ? $targets[$result] : $result;
                        return $object->webSocketDebuggerUrl;
                    });
        }
//         }
//         default:
//             throw new Error(`Invalid target argument "${this.target}"`);
//         }
    }

    // fetch the protocol according to 'protocol' and 'local'
    // async
    public function _fetchProtocol($options) {
//         // if a protocol has been provided then use it
//         if (this.protocol) {
//             return this.protocol;
//         }
//         // otherwise user either the local or the remote version
//         else {
//             options.local = this.local;
//             return await devtools.Protocol(options);
//         }
    }

    // establish the WebSocket connection and start processing user commands
    private function _connectToWebSocket() {
//         return new Promise((fulfill, reject) => {
//             // create the WebSocket
//             try {
//                 if (this.secure) {
//                     this.webSocketUrl = this.webSocketUrl.replace(/^ws:/i, 'wss:');
//                 }
//                 this._ws = new WebSocket(this.webSocketUrl, [], {
//                     maxPayload: 256 * 1024 * 1024,
//                     perMessageDeflate: false,
//                     followRedirects: true,
//                 });
//             } catch (err) {
//                 // handles bad URLs
//                 reject(err);
//                 return;
//             }
//             // set up event handlers
//             this._ws.on('open', () => {
//                 fulfill();
//             });
//             this._ws.on('message', (data) => {
//                 const message = JSON.parse(data);
//                 this._handleMessage(message);
//             });
//             this._ws.on('close', (code) => {
//                 this._handleConnectionClose();
//                 this.emit('disconnect');
//             });
//             this._ws.on('error', (err) => {
//                 reject(err);
//             });
//         });
    }

    private function _handleConnectionClose() {
//         // make sure to complete all the unresolved callbacks
//         const err = new Error('WebSocket connection closed');
//         for (const callback of Object.values(this._callbacks)) {
//             callback(err);
//         }
//         this._callbacks = {};
    }

    // handle the messages read from the WebSocket
    private function _handleMessage($message) {
//         // command response
//         if (message.id) {
//             const callback = this._callbacks[message.id];
//             if (!callback) {
//                 return;
//             }
//             // interpret the lack of both 'error' and 'result' as success
//             // (this may happen with node-inspector)
//             if (message.error) {
//                 callback(true, message.error);
//             } else {
//                 callback(false, message.result || {});
//             }
//             // unregister command response callback
//             delete this._callbacks[message.id];
//             // notify when there are no more pending commands
//             if (Object.keys(this._callbacks).length === 0) {
//                 this.emit('ready');
//             }
//         }
//         // event
//         else if (message.method) {
//             const {method, params, sessionId} = message;
//             this.emit('event', message);
//             this.emit(method, params, sessionId);
//             this.emit(`${method}.${sessionId}`, params, sessionId);
//         }
    }

    // send a command to the remote endpoint and register a callback for the reply
    private function _enqueueCommand($method, $params, $sessionId, $callback) {
//         const id = this._nextCommandId++;
//         const message = {
//             id,
//             method,
//             sessionId,
//             params: params || {}
//         };
//         this._ws.send(JSON.stringify(message), (err) => {
//             if (err) {
//                 // handle low-level WebSocket errors
//                 if (typeof callback === 'function') {
//                     callback(err);
//                 }
//             } else {
//                 this._callbacks[id] = callback;
//             }
//         });
    }
}


