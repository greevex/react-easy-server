<?php

use greevex\react\easyServer\examples\exampleServer;

require_once __DIR__ . '/../../tests/bootstrap.php';

$clientConfig = [
    'host' => '127.0.0.1',
    'port' => 9089,
    'protocol' => exampleServer\exampleProtocol::class,
    'communication' => exampleServer\exampleCommunication::class,
];

$loop = \React\EventLoop\Factory::create();

error_log('[client] Connecting to server...');

$socket = stream_socket_client("{$clientConfig['host']}:{$clientConfig['port']}");

error_log('[client] Connected, preparing client object...');

$client = new \greevex\react\easyServer\client($socket, $loop, new $clientConfig['protocol']);
$client->pause();
$client->on('command', function($response, \greevex\react\easyServer\client $client) {
    if($response['request'] === 'set') {
        // store confirmation
        error_log(sprintf('[%5.5f][STORE] key:%s', microtime(true), $response['data']['key']));
    } elseif($response['request'] === 'get') {
        // requested key status received
        error_log(sprintf('[%5.5f][<<<<<] key:%s value:%s', microtime(true), $response['data']['key'], $response['data']['value']));
    } else {
        error_log('Unknown response from server: ' . json_encode($response));
    }
});
$client->resume();

$period = 1.0;

$loop->addPeriodicTimer($period, function() use ($client, $loop, $period) {
    $key = md5(microtime());;
    $value = mt_rand(0,1000);
    error_log(sprintf('[%5.5f][>>>>>] key:%s value:%s', microtime(true), $key, $value));
    // Setting value for key
    $client->send([
        'request' => 'set',
        'data' => [
            'key' => $key,
            'value' => $value
        ],
    ]);
    // Request value of key
    $loop->addTimer($period / 4, function() use ($client, $key) {
        error_log(sprintf('[%5.5f][ GET ] key:%s', microtime(true), $key));
        $client->send([
            'request' => 'get',
            'data' => [
                'key' => $key
            ]
        ]);
    });
});

$loop->run();