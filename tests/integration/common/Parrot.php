<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains a management class to stand up and tear down the HTTP
 * server used for integration tests.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher\IntegrationTest;

use GuzzleHttp\Client;
use SebastianBergmann\Environment\Runtime;

/**
 * A class to manage spawning and terminating the HTTP server that parrots back
 * whatever it gets sent.
 */
class Parrot
{
    protected $child;
    protected $pipes;
    protected $port;

    /**
     * Spawns a HTTP server.
     */
    public function __construct()
    {
        $runtime = new Runtime();
        $cmd = sprintf(
            '%s %s',
            escapeshellcmd($runtime->getBinary()),
            escapeshellarg(dirname(__FILE__) . '/../server/server.php')
        );

        $this->child = proc_open(
            $cmd,
            [
                ['pipe', 'r'],
                ['pipe', 'w'],
                // We capture the stderr pipe, but don't actually do anything
                // with it. A future enhancement might be to dump this on
                // error.
                ['pipe', 'w'],
            ],
            $this->pipes
        );

        // The server outputs a JSON object as the first line of output on
        // stdout, so we'll grab that and parse out the port number.
        $this->port = (int) json_decode(trim(fgets($this->pipes[1])))->port;
    }

    /**
     * Stops the HTTP server.
     */
    public function __destruct()
    {
        proc_terminate($this->child);
    }

    /**
     * Returns the port the HTTP server is listening on.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Returns the last post that was sent to the Log API endpoint on the HTTP
     * server.
     *
     * @return array The exact format of this is defined by the server, but it
     *               should at least include a "headers" key with the request
     *               headers, and a "body" key with the request body.
     */
    public function getLastPost()
    {
        $client = new Client();
        $response = $client->get("http://127.0.0.1:{$this->port}/last");
        return json_decode($response->getBody()->getContents(), true);
    }
}
