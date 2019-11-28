<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the tests for the New Relic Monolog Enricher
 * Handler.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher;

use PHPUnit_Framework_TestCase;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;

class HandlerTest extends PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // log message

        $record = array(
            'message' => 'test',
            'context' => array(),
            'level' => 300,
            'level_name' => 'WARNING',
            'channel' => 'test',
            'extra' => array(),
            'datetime' => new \DateTime("now", new \DateTimeZone("UTC")),
        );

        $formatter = new Formatter(Formatter::BATCH_MODE_JSON, false);
        $data = $formatter->format($record);

        $expected = array();


        // perform tests
        /*
        $handler = new Handler();
        $handler->handle($record);
        $handler->handleBatch([$msg]);
         */
    }

    public function testSetFormatter()
    {
        $handler = new Handler();
        $formatter = new Formatter();
        $handler->setFormatter($formatter);
        $this->assertInstanceOf(
            'NewRelic\Monolog\Enricher\Formatter',
            $handler->getFormatter()
        );
    }

    public function testDefaultFormatterConfig()
    {
        $handler = new Handler();
        $formatter = $handler->getFormatter();
        $this->assertEquals(
            Formatter::BATCH_MODE_JSON,
            $formatter->getBatchMode()
        );
        $this->assertEquals(false, $formatter->isAppendingNewlines());
    }

    public function testSetFormatterInvalid()
    {
        $handler = new Handler();
        $formatter = new NormalizerFormatter();

        $this->setExpectedException(
            'InvalidArgumentException',
            'NewRelic\Monolog\Enricher\Handler is only compatible with '
            . 'NewRelic\Monolog\Enricher\Formatter'
        );

        $handler->setFormatter($formatter);
    }
}
