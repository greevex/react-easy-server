<?php

namespace greevex\react\easyServer\examples\exampleServer;

use greevex\react\easyServer\communication\abstractCommunication;

class exampleCommunication
    extends abstractCommunication
{

    /**
     * Prepare on new object initialization
     */
    protected function prepare()
    {
        $this->session['storage'] = [];
    }

    /**
     * Process new received client command
     *
     * @param $command
     */
    protected function clientCommand($command)
    {
        switch($command['request']) {
            case 'set':
                if(!isset($command['data']['key'], $command['data']['value'])) {
                    $this->client->send([
                        'request' => 'set',
                        'data' => [
                            'stored' => false,
                            'error' => 'invalid request, required params `key` or/and `value` missed'
                        ],
                    ]);
                    break;
                }
                $this->session['storage'][$command['data']['key']] = $command['data']['value'];
                $this->client->send([
                    'request' => 'set',
                    'data' => [
                        'key' => $command['data']['key'],
                        'stored' => true,
                    ],
                ]);
                break;
            case 'get':
                if(!array_key_exists($command['data']['key'], $this->session['storage'])) {
                    $this->client->send([
                        'request' => 'get',
                        'data' => [
                            'status' => false,
                            'key' => $command['data']['key'],
                            'error' => 'key not found'
                        ],
                    ]);
                }
                $this->client->send([
                    'request' => 'get',
                    'data' => [
                        'status' => true,
                        'key' => $command['data']['key'],
                        'value' => $this->session['storage'][$command['data']['key']]
                    ],
                ]);
                break;
            case 'echo':
                $this->client->send($command);
                break;
            default:
                $this->client->send([
                    'request' => 'unknown_command'
                ]);
                break;
        }
    }
}