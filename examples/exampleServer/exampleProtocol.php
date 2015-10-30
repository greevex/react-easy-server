<?php

namespace greevex\react\easyServer\examples\exampleServer;

use greevex\react\easyServer\protocol\abstractProtocol;

class exampleProtocol
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
        while(($pos = strpos($this->buffer, "\n")) !== false) {
            $line = substr($this->buffer, 0, $pos);
            $this->buffer = substr($this->buffer, $pos + 1);
            $command = json_decode($line, true);
            if (!is_array($command)) {
                //ignore invalid command
                continue;
            }
            $result[] = $command;
        }

        return $result;
    }

    /**
     * Build bytes from command to send it
     *
     * @param mixed $command
     *
     * @return string
     * @throws exampleProtocolException
     */
    public function prepareCommand($command)
    {
        if(!isset($command['request'], $command['data'])) {
            throw new exampleProtocolException('Invalid request');
        }

        return json_encode($command) . "\n";
    }
}

class exampleProtocolException extends \Exception {}