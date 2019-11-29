<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the HTTP server used to fake the Log API endpoint in the
 * integration tests.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;

require dirname(__FILE__) . '/vendor/autoload.php';

// Set up the event loop.
$loop = Factory::create();

// Set up a basic route dispatcher.
$dispatcher = \FastRoute\simpleDispatcher(
    function (RouteCollector $routes) use ($loop) {
        $lastPost = null;

        // Save POSTs to /log/v1 to $lastPost.
        $routes->post(
            '/log/v1',
            function (ServerRequestInterface $request) use (&$lastPost) {
                $lastPost = [
                    'body'    => $request->getBody()->getContents(),
                    'headers' => $request->getHeaders(),
                ];

                return new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(true)
                );
            }
        );

        // Return the contents of $lastPost.
        $routes->get(
            '/last',
            function (ServerRequestInterface $request) use (&$lastPost) {
                if (is_array($lastPost)) {
                    return new Response(
                        200,
                        ['Content-Type' => 'application/json'],
                        json_encode($lastPost)
                    );
                } elseif (is_null($lastPost)) {
                    return new Response(
                        404,
                        ['Content-Type' => 'text/plain'],
                        'Nothing has been posted to this endpoint'
                    );
                }

                return new Response(
                    500,
                    ['Content-Type' => 'text/plain'],
                    'Invalid last post of type: ' . gettype($lastPost)
                );
            }
        );
    }
);

$server = new HttpServer(
    function (ServerRequestInterface $request) use ($dispatcher) {
        // This callback glues together the FastRoute dispatcher and the React
        // HTTP server so that routing occurs.
        $path = $request->getUri()->getPath();
        $routeInfo = $dispatcher->dispatch($request->getMethod(), $path);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response = new Response(
                    404,
                    ['Content-Type' => 'text/plain'],
                    'Not found'
                );
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = new Response(
                    405,
                    ['Content-Type' => 'text/plain'],
                    'Method not allowed'
                );
                break;

            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $response = $handler($request);
                break;

            default:
                $response = new Response(
                    500,
                    ['Content-Type' => 'text/plain'],
                    "Error in routing: {$routeInfo[0]}"
                );
                break;
        }

        // Output an access log line to stderr.
        $size = $response->getBody()->getSize();
        fprintf(
            STDERR,
            "%s - - [%s] \"%s %s\" %d %s\n",
            $request->getServerParams()['REMOTE_ADDR'],
            date('d/M/Y:H:i:s O'),
            $request->getMethod(),
            $path,
            $response->getStatusCode(),
            is_null($size) ? '-' : strval($size)
        );

        return $response;
    }
);

// Listen on a random port.
$socket = new SocketServer('0.0.0.0:0', $loop);
$server->listen($socket);

// Set up a timer to output the port to stdout so the caller knows how to talk
// to this server once it's up and running.
$loop->futureTick(function () use ($socket) {
    echo json_encode([
        'port' => parse_url($socket->getAddress(), PHP_URL_PORT),
    ]) . "\n";
    flush();
});

// Actually run the event loop.
$loop->run();
