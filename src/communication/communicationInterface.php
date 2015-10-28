<?php

namespace greevex\react\easyServer\communication;

use Evenement\EventEmitterInterface;

/**
 * Communication interface
 *
 * @package greevex\react\easyServer\communication
 * @author Gregory Ostrovsky <greevex@gmail.com>
 */
interface communicationInterface
    extends EventEmitterInterface
{
    /**
     * @return string
     */
    public function getId();
}