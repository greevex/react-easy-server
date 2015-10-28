<?php

namespace greevex\react\easyServer\communication;

/**
 * Abstract communication scenario
 *
 * Class describes structured communication between client and server
 *
 * @package greevex\react\easyServer\communication
 * @author Gregory Ostrovsky <greevex@gmail.com>
 */
class pingPongCommunication
    extends abstractCommunication
{
    /**
     * Prepare on new object initialization
     */
    protected function prepare()
    {
        $this->on('connected', function() {
            // Hello new client
            $this->client->send([
                'request' => 'ping',
                'payload' => [
                    'time' => microtime(true),
                ],
            ]);
        });
    }

    /**
     * Process new received client command
     *
     * @param $command
     */
    protected function clientCommand($command)
    {
        switch($command['request']) {
            case 'ping':
                $time = microtime(true);
                $writeLatency = $time - $command['payload']['time'];
                error_log('[ping] Write latency: ' . ((int)$writeLatency*1000) . 'ms');
                if(!empty($command['payload']['latency'])) {
                    error_log('[ping] Received latency: ' . ((int)$command['payload']['latency']*1000) . 'ms');
                }
                $this->client->send([
                    'request' => 'pong',
                    'payload' => [
                        'time' => $time,
                        'latency' => $writeLatency,
                    ],
                ]);
                break;
            case 'pong':
                $time = microtime(true);
                $readLatency = $time - $command['payload']['time'];
                error_log('[pong] Read latency: ' . ((int)$readLatency*1000) . 'ms');
                if(!empty($command['payload']['latency'])) {
                    error_log('[pong] Received latency: ' . ((int)$command['payload']['latency']*1000) . 'ms');
                }
                $this->client->send([
                    'request' => 'ping',
                    'payload' => [
                        'time' => $time,
                        'latency' => $readLatency,
                    ],
                ]);
                break;
            case 'getTime':
                $time = microtime(true);
                $this->client->send([
                    'request' => 'time',
                    'payload' => [
                        'unixtime' => $time,
                        'w3c' => date(DATE_W3C, $time),
                    ],
                ]);
                break;
            default:
                error_log('Unknown command');
                $this->client->send([
                    'request' => 'unknown_command',
                ]);
                break;
        }
    }
}