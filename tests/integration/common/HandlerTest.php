<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the common handler integration tests shared across
 * Monolog versions.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace {
    if (!function_exists('newrelic_get_linking_metadata')) {
        // This function won't actually be used, but is required to ensure
        // that the Processor actually tries to call the namespaced version.
        function newrelic_get_linking_metadata()
        {
            return NewRelic\Monolog\Enricher\newrelic_get_linking_metadata();
        }
    }
}
namespace NewRelic\Monolog\Enricher {
    // This function provides the effective mock for
    // newrelic_get_linking_metadata().
    function newrelic_get_linking_metadata()
    {
        return array('hostname' => 'example.host',
                     'entity.name' => 'Processor Tests',
                     'entity.type' => 'SERVICE');
    }
}
namespace NewRelic\Monolog\Enricher\IntegrationTest {
    use Monolog\Handler\BufferHandler;
    use Monolog\Logger;
    use NewRelic\Monolog\Enricher\Processor;

    require_once dirname(__FILE__) . '/TestCase.php';

    /**
     * The common handler tests between Monolog 1 and 2.
     */
    class HandlerTest extends TestCase
    {
        public function testBatchNoProcessor()
        {
            $logger = new Logger('test');
            $buffer = new BufferHandler($this->handler);
            $logger->pushHandler($buffer);

            $logger->error('uh oh');
            $logger->warning('ruh roh');

            $buffer->flush();

            $last = $this->getLastPost();
            $this->assertContentType('application/json', $last['headers']);
            $this->assertLicenseKey($this->key, $last['headers']);

            $expected = [
                [
                    'message'     => 'uh oh',
                    'context'     => [],
                    'level'       => 400,
                    'level_name'  => 'ERROR',
                    'channel'     => 'test',
                    'extra'       => [],
                ],
                [
                    'message'     => 'ruh roh',
                    'context'     => [],
                    'level'       => 300,
                    'level_name'  => 'WARNING',
                    'channel'     => 'test',
                    'extra'       => [],
                ],
            ];
            $this->assertLogBatch($expected, $last['body']);
        }

        public function testBatchWithProcessor()
        {
            $logger = new Logger('test');
            $buffer = new BufferHandler($this->handler);
            $logger->pushHandler($buffer);
            $logger->pushProcessor(new Processor());

            $logger->error('uh oh');
            $logger->warning('ruh roh');

            $buffer->flush();

            $last = $this->getLastPost();
            $this->assertContentType('application/json', $last['headers']);
            $this->assertLicenseKey($this->key, $last['headers']);

            $expected = [
                [
                    'message'     => 'uh oh',
                    'context'     => [],
                    'level'       => 400,
                    'level_name'  => 'ERROR',
                    'channel'     => 'test',
                    'extra'       => [],
                    'hostname'    => 'example.host',
                    'entity.name' => 'Processor Tests',
                    'entity.type' => 'SERVICE',
                ],
                [
                    'message'     => 'ruh roh',
                    'context'     => [],
                    'level'       => 300,
                    'level_name'  => 'WARNING',
                    'channel'     => 'test',
                    'extra'       => [],
                    'hostname'    => 'example.host',
                    'entity.name' => 'Processor Tests',
                    'entity.type' => 'SERVICE',
                ],
            ];
            $this->assertLogBatch($expected, $this->getLastPost()['body']);
        }

        public function testSingleNoProcessor()
        {
            $logger = new Logger('test');
            $logger->pushHandler($this->handler);

            $logger->error('uh oh');

            $last = $this->getLastPost();
            $this->assertContentType('application/json', $last['headers']);
            $this->assertLicenseKey($this->key, $last['headers']);

            $expected = [
                'message'     => 'uh oh',
                'context'     => [],
                'level'       => 400,
                'level_name'  => 'ERROR',
                'channel'     => 'test',
                'extra'       => [],
            ];
            $this->assertLogRecord($expected, $this->getLastPost()['body']);
        }

        public function testSingleWithProcessor()
        {
            $logger = new Logger('test');
            $logger->pushHandler($this->handler);
            $logger->pushProcessor(new Processor());

            $logger->error('uh oh');

            $last = $this->getLastPost();
            $this->assertContentType('application/json', $last['headers']);
            $this->assertLicenseKey($this->key, $last['headers']);

            $expected = [
                'message'     => 'uh oh',
                'context'     => [],
                'level'       => 400,
                'level_name'  => 'ERROR',
                'channel'     => 'test',
                'extra'       => [],
                'hostname'    => 'example.host',
                'entity.name' => 'Processor Tests',
                'entity.type' => 'SERVICE',
            ];
            $this->assertLogRecord($expected, $this->getLastPost()['body']);
        }
    }
}
