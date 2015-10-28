<?php

namespace greevex\react\easyServer\protocol;

use Evenement\EventEmitterInterface;

/**
 * Abstract protocol interface
 *
 * @package greevex\react\easyServer\protocol
 * @author Gregory Ostrovsky <greevex@gmail.com>
 */
interface protocolInterface
    extends EventEmitterInterface
{

    /**
     * Update buffer with new read data and try to parse it
     *
     * @param string $data
     */
    public function onData($data);

    /**
     * Build bytes from command to send it
     *
     * @param mixed $command
     *
     * @return string
     */
    public function prepareCommand($command);
}