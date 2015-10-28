<?php

namespace greevex\react\easyServer\communication;

use React;
use Evenement\EventEmitter;
use greevex\react\easyServer\client;

/**
 * Abstract client communication scenario
 *
 * Class describes structured communication between client and server
 *
 * Events:
 *  connected - client connected, but paused. WARN: Do not resume it here!
 *  disconnected - client was disconnected, abort all tasks with this client
 *  command - client command received. WARN: Do not use it event inside object, cuz method clientCommand() exists
 *
 * @package greevex\react\easyServer\communication
 * @author Gregory Ostrovsky <greevex@gmail.com>
 */
abstract class abstractCommunication
    extends EventEmitter
    implements communicationInterface
{
    const SCENARIO_ID_POSTFIX = ':scenario';

    /**
     * @var React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var client
     */
    protected $client;

    /**
     * @var string
     */
    protected $id;

    /**
     * Client communication session data
     *
     * @var array
     */
    protected $session = [];

    /**
     * @param React\EventLoop\LoopInterface $loop
     * @param client                        $client
     */
    public function __construct(React\EventLoop\LoopInterface $loop, client $client)
    {
        $this->loop = $loop;
        $this->client = $client;
        $this->id = $client->getId() . self::SCENARIO_ID_POSTFIX;
        $this->client->on('command', function($command) {
            $this->emit('command', [$command, $this->client]);
            $this->clientCommand($command);
        });
        $this->prepare();
    }

    /**
     * Prepare on new object initialization
     */
    abstract protected function prepare();

    /**
     * Process new received client command
     *
     * @param $command
     */
    abstract protected function clientCommand($command);

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}