<?php

namespace greevex\react\easyServer\protocol;

/**
 * gzJsonProtocol - gzipped json string protocol
 *
 * @package greevex\react\easyServer\protocol
 * @author Gregory Ostrovsky <greevex@gmail.com>
 */
class gzJsonProtocol
    extends abstractProtocol
{

    /**
     * Try to parse something from buffer
     *
     * @return array List of parsed commands
     */
    protected function tryParseCommands()
    {
        $result = [];

        for($dataLen = strlen($this->buffer); $dataLen >= 4;) {
            // reading length of received command
            $commandLenBytes = substr($this->buffer, 0, 4);
            $commandLen = intval(bin2hex($commandLenBytes), 16);
            if($dataLen < $commandLen+4) {
                // waiting more bytes to parse
                break;
            }
            $commandBytes = gzuncompress(substr($this->buffer, 4, $commandLen));
            $command = json_decode($commandBytes, true);
            $result[] = $command;
            // cut parsed bytes from buffer
            $this->buffer = substr($this->buffer, $commandLen + 4);
        }

        return $result;
    }

    /**
     * Build bytes from command to send it
     * 4 bytes - length of command
     * some bytes - encoded command
     *
     * @param mixed $command
     *
     * @return string
     */
    public function prepareCommand($command)
    {
        $encodedCommand = gzcompress(json_encode($command), 1);

        return hex2bin(str_pad(dechex(strlen($encodedCommand)), 8, '0', STR_PAD_LEFT)) . $encodedCommand;
    }
}