<?php
namespace greevex\react\tests\easyServer;

require_once __DIR__ . '/bootstrap.php';

use greevex\react\easyServer\communication\pingPongCommunication;
use greevex\react\easyServer\protocol\gzJsonProtocol;
use React\EventLoop\StreamSelectLoop;
use greevex\react\easyServer\server\easyServer;

class ServerTest extends testCase
{

    /**
     * @var StreamSelectLoop
     */
    private $loop;
    /**
     * @var easyServer
     */
    private $server;
    private $port;
    private $config = [
        'host' => '127.0.0.1',
        'port' => 9088,
        'protocol' => gzJsonProtocol::class,
        'communication' => pingPongCommunication::class,

    ];
    private function createLoop()
    {
        return new StreamSelectLoop();
    }
    /**
     * @covers React\Socket\Server::__construct
     * @covers React\Socket\Server::listen
     * @covers React\Socket\Server::getPort
     */
    public function setUp()
    {
        $this->port = $this->config['port'];
        $this->loop = $this->createLoop();
        $this->server = new easyServer($this->loop);
        $this->server->setConfig($this->config);
        $this->server->start();
    }
    /**
     * @covers React\EventLoop\StreamSelectLoop::tick
     * @covers React\Socket\Server::handleConnection
     * @covers React\Socket\Server::createConnection
     */
    public function testConnection()
    {
        stream_socket_client('tcp://localhost:'.$this->port);
        $called = 0;
        $this->server->on('connection', function() use (&$called) {
            $called++;
        });
        $this->loop->tick();
        static::assertEquals(1, $called);
    }
    /**
     * @covers easyServer::start
     * @covers easyServer::getClients
     * @covers easyServer::getCommunications
     * @covers easyServer::getCommunications
     */
    public function testConnectionWithManyClients()
    {
        stream_socket_client('tcp://localhost:'.$this->port);
        stream_socket_client('tcp://localhost:'.$this->port);
        stream_socket_client('tcp://localhost:'.$this->port);
        $called = 0;
        $this->server->on('connection', function() use (&$called) {
            $called++;
        });
        $this->loop->tick();
        $this->loop->tick();
        $this->loop->tick();
        static::assertEquals(3, $called);
    }

    /**
     * @covers React\Socket\Server::shutdown
     */
    public function tearDown()
    {
        if ($this->server) {
            $this->server->shutdown();
        }
    }
}