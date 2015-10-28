<?php

namespace greevex\react\easyServer\server;

use React;
use Evenement\EventEmitter;
use greevex\react\easyServer\client;
use greevex\react\easyServer\protocol\protocolInterface;
use greevex\react\easyServer\communication\communicationInterface;

/**
 * Easy server implementation
 *
 * Build any socket server with any protocol based on this class
 * Set protocol in the config to wrap all low-level parsing/encoding
 * Set communication in the config to wrap all client-oriented communication scenario with sessions
 * No more actions required in this server, all work with every client would be in their communication objects
 *
 * Events:
 *  starting - method start() was called. Params: [easyServer $this]
 *  started - server was started and already listen port. Params: [easyServer $this]
 *  connection - new client connected. Params: none
 *
 * @package greevex\react\easyServer\server
 * @author Gregory Ostrovsky <greevex@gmail.com>
 */
class easyServer
    extends EventEmitter
{
    /**
     * @var React\Socket\Server
     */
    private $socketServerInstance;

    /**
     * @var React\EventLoop\ExtEventLoop|React\EventLoop\LibEventLoop|React\EventLoop\LibEvLoop|React\EventLoop\StreamSelectLoop
     */
    protected $loop;

    /**
     * @var array
     */
    protected $config = [
        'host' => '127.0.0.1',
        'port' => 12345,
        'protocol' => null,
        'communication' => null,
    ];

    /**
     * @var client[] array
     */
    protected $clients = [];

    /**
     * @var communicationInterface[] array
     */
    protected $communications = [];

    /**
     * @param React\EventLoop\LoopInterface $loop
     */
    public function __construct(React\EventLoop\LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * Start server
     *
     * @throws React\Socket\ConnectionException
     */
    public function start()
    {
        $this->emit('starting', [$this]);
        $socketServer = $this->socketServer();
        $socketServer->on('connection', function(React\Socket\Connection $clientConnection) {
            $clientConnection->pause();
            $newClient = new client($clientConnection->stream, $this->loop, new $this->config['protocol']);
            $clientId = $newClient->getId();
            $this->clients[$clientId] = $newClient;
            /** @var communicationInterface $communication */
            $communicationClass = $this->config['communication'];
            $communication = new $communicationClass($this->loop, $newClient);
            $this->communications[$communication->getId()] = $communication;
            $newClient->on('close', function() use ($newClient, $communication) {
                $communication->emit('disconnected');
                unset($this->communications[$communication->getId()], $this->clients[$newClient->getId()]);
            });
            $newClient->resume();
            $this->emit('connection', [$newClient, $communication]);
            $communication->emit('connected');
        });
        $socketServer->listen($this->config['port'], $this->config['host']);
        $this->emit('started', [$this]);
    }

    /**
     * Set server config
     *
     * @param $config
     *
     * @inner-param string host
     * @inner-param int port
     * @inner-param protocolInterface protocol
     * @inner-param communicationInterface communication
     *
     * @throws easyServerException
     */
    public function setConfig($config)
    {
        $this->config = array_replace($this->config, $config);
        if($this->config['protocol'] === null) {
            throw new easyServerException("'protocol' section must be specified in easyServer config");
        }
        if($this->config['communication'] === null) {
            throw new easyServerException("'communication' section must be specified in easyServer config");
        }
        if(!is_a($this->config['protocol'], protocolInterface::class)) {
            throw new easyServerException("'protocol' must implements " . protocolInterface::class);
        }
        if(!is_a($this->config['communication'], communicationInterface::class)) {
            throw new easyServerException("'communication' must implements " . communicationInterface::class);
        }
    }

    /**
     * @return React\Socket\Server
     */
    protected function socketServer()
    {
        if($this->socketServerInstance === null) {
            $this->socketServerInstance = new React\Socket\Server($this->loop);
        }

        return $this->socketServerInstance;
    }
}