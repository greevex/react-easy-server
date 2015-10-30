<?php

use React\EventLoop;
use greevex\react\easyServer\server\easyServer;
use greevex\react\easyServer\examples\exampleServer;

require_once __DIR__ . '/bootstrap.php';

$serverConfig = [
    'host' => '127.0.0.1',
    'port' => 9089,
    'protocol' => exampleServer\exampleProtocol::class,
    'communication' => exampleServer\exampleCommunication::class,
];

$loop = EventLoop\Factory::create();

error_log('[server] Starting...');

$server = new easyServer($loop);
$server->setConfig($serverConfig);
$server->start();

$loop->run();