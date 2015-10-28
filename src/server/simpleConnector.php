<?php

namespace greevex\react\easyServer\server;

use React;

/**
 * Class simpleConnector
 *
 * @package greevex\react\easyServer\server
 * @author Gregory Ostrovsky <greevex@gmail.com>
 */
class simpleConnector
{

    /**
     * @var React\EventLoop\LoopInterface
     */
    private $loop;

    /**
     * @var array
     */
    private $config = [
        'dns' => '8.8.8.8',
    ];

    /**
     * @var React\SocketClient\Connector
     */
    protected $connector;

    /**
     * @param React\EventLoop\LoopInterface $loop
     * @param array                         $config
     */
    public function __construct(React\EventLoop\LoopInterface $loop, array $config = null)
    {
        $this->loop = $loop;
        if($config !== null) {
            $this->config = $config;
        }
    }

    /**
     * @return React\SocketClient\Connector
     */
    public function getConnector()
    {
        if($this->connector === null) {
            $dnsResolverFactory = new React\Dns\Resolver\Factory();
            $dns = $dnsResolverFactory->createCached($this->config['dns'], $this->loop);
            $this->connector = new React\SocketClient\Connector($this->loop, $dns);
        }

        return $this->connector;
    }
}