<?php
/**
 * @author greevex
 * @date: 10/28/15 8:57 PM
 */

require_once '../tests/bootstrap.php';

$loop = \React\EventLoop\Factory::create();

$serverConfig = [
    'host' => '127.0.0.1',
    'port' => 9089,
    'protocol' => \greevex\react\easyServer\protocol\gzJsonProtocol::class,
    'communication' => \greevex\react\easyServer\communication\pingPongCommunication::class,
];

$server = new greevex\react\easyServer\server\easyServer($loop);
$server->setConfig($serverConfig);
$server->start();

// that's all for a server :)

// and now create client that requests time via gzJsProtocol and pingPongCommunication commands
// after time received let it ping pong ping pong ping pong ping pong... between client and server
$loop->addTimer(1, function() use ($loop, $serverConfig) {
    $connector = new \greevex\react\easyServer\server\simpleConnector($loop);
    $connector->getConnector()
              ->create($serverConfig['host'] === '0.0.0.0' ? '127.0.0.1' : $serverConfig['host'], $serverConfig['port'])
              ->then(function(\React\Stream\Stream $connectionStream) use ($loop, $serverConfig) {
                  $client = new \greevex\react\easyServer\client($connectionStream->stream, $loop, new $serverConfig['protocol']);
                  $client->pause();
                  $client->on('command', function($response, \greevex\react\easyServer\client $client) {
                      switch($response['request']) {
                          case 'time':
                              error_log("Time received: {$response['payload']['w3c']}");
                              $command = [
                                  'request' => 'ping',
                                  'payload' => [
                                      'time' => microtime(true)
                                  ]
                              ];
                              error_log('Sending first ping command.');
                              $client->send($command);
                              break;
                          case 'pong':
                              error_log('Latency: ' . round($response['payload']['latency'], 6));
                              usleep(500000);
                              $command = [
                                  'request' => 'ping',
                                  'payload' => [
                                      'time' => microtime(true)
                                  ]
                              ];
                              $client->send($command);
                              break;
                      }
                  });
                  $client->resume();

                  $command = [
                      'request' => 'getTime'
                  ];
                  error_log('Sending command: ' . json_encode($command));
                  $client->send($command);
                  $client->resume();
              });
});

$loop->run();