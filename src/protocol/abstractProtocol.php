<?php

namespace greevex\react\easyServer\protocol;

use React;
use Evenement\EventEmitter;

/**
 * Protocol parser/builder abstraction
 *
 * @package greevex\react\easyServer\protocol
 */
abstract class abstractProtocol
    extends EventEmitter
{

    /**
     * Stream reading buffer
     *
     * @var string
     */
    protected $buffer = '';

    /**
     * Update buffer with new read data and try to parse it
     *
     * @param string $data
     */
    public function onData($data) {
        $this->buffer .= $data;
        $commands = $this->tryParseCommands();
        foreach($commands as $command) {
            $this->emit('command', [$command]);
        }
    }

    /**
     * Try to parse something from buffer
     *
     * @return array List of parsed commands
     */
    abstract protected function tryParseCommands();

    /**
     * Build bytes from command to send it
     *
     * @param mixed $command
     *
     * @return string
     */
    abstract public function prepareCommand($command);
}