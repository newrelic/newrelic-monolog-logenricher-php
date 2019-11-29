<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains helper methods common to all handler test cases.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher\IntegrationTest;

use PHPUnit_Framework_TestCase;

// phpcs:disable
require_once dirname(__FILE__) . '/InsecureHandler.php';
require_once dirname(__FILE__) . '/Parrot.php';
// phpcs:enable

/**
 * Helper methods common to all handler test cases.
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /** A Monolog handler configured to talk to the parrot server. */
    protected $handler;

    /** The parrot object. */
    private $parrot;

    /** The licence key to use when testing. */
    protected $key = '0123456789012345678901234567890123456789';

    public function setUp()
    {
        $this->parrot = new Parrot();

        $this->handler = new InsecureHandler();
        $this->handler->setLicenseKey($this->key);
        $this->handler->setHost('127.0.0.1:' . $this->parrot->getPort());
    }

    public function tearDown()
    {
        unset($this->handler);
        unset($this->parrot);
    }

    protected function getLastPost()
    {
        return $this->parrot->getLastPost();
    }

    /**
     * Assert that the given headers include a particular content type.
     *
     * @param string $type
     * @param array  $headers
     * @param string $msg
     */
    protected function assertContentType($type, array $headers, $msg = null)
    {
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertCount(1, $headers['Content-Type']);
        $this->assertSame($type, $headers['Content-Type'][0], $msg);
    }

    /**
     * Assert that the given headers include a particular licence key.
     *
     * @param string $type
     * @param array  $headers
     * @param string $msg
     */
    protected function assertLicenseKey($key, array $headers, $msg = null)
    {
        $this->assertArrayHasKey('X-License-Key', $headers);
        $this->assertCount(1, $headers['X-License-Key']);
        $this->assertSame($key, $headers['X-License-Key'][0], $msg);
    }

    /**
     * Assert that the log batch contains the expected records.
     *
     * @param array  $expected
     * @param string $actual   The actual JSON for the log batch.
     * @param string $msg
     */
    protected function assertLogBatch(array $expected, $actual, $msg = null)
    {
        $expected = $this->sanitiseLogBatch($expected);
        $actual = $this->sanitiseLogBatch(json_decode($actual, true));

        $this->assertEquals($expected, $actual, $msg);
    }

    /**
     * Assert that the log record matches the expected record.
     *
     * @param array  $expected
     * @param string $actual   The actual JSON for the log record.
     * @param string $msg
     */
    protected function assertLogRecord(array $expected, $actual, $msg = null)
    {
        $expected = $this->sanitiseLogRecord($expected, true);
        $actual = $this->sanitiseLogRecord(json_decode($actual, true));

        $this->assertEquals($expected, $actual, $msg);
    }

    protected function sanitiseLogBatch(array $batch)
    {
        return array_map(
            function (array $record) {
                return $this->sanitiseLogRecord($record);
            },
            $batch
        );
    }

    protected function sanitiseLogRecord(array $record)
    {
        // Fields that we're going to ignore, since they're non-deterministic.
        foreach (['datetime', 'timestamp'] as $field) {
            $record[$field] = 'xxx';
        }

        return $record;
    }
}
