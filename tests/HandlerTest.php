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

use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\MissingExtensionException;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class HandlerTest extends PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
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
        $expected = $formatter->format($record);
        $this->expectOutputString($expected);
        $handler = new StubHandler();
        $handler->handle($record);
    }

    public function testHandleBatch()
    {
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
        $expected = $formatter->formatBatch(array($record));
        $this->expectOutputString($expected);
        $handler = new StubHandler();
        $handler->handleBatch([$record]);
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

    public function testMissingCurlExtension()
    {
        $this->setExpectedException(
            'Monolog\Handler\MissingExtensionException',
            'The curl extension is required to use this Handler'
        );
        
        $GLOBALS['extension_loaded'] = false;
        $handler = new Handler();
        $GLOBALS['extension_loaded'] = true;
    }
}

// phpcs:disable
/**
 * Stubhandler overrides the methods of Handler that normally call
 * curl_exec, and instead outputs the data they receive
 */
class StubHandler extends Handler {
    protected function send($data)
    {
        print($data);
    }

    protected function sendBatch($data)
    {
        print($data);
    }
}

/**
 * Mocks global function extension_loaded to return the value
 * of global variable $extension_loaded
 */
function extension_loaded($extension)
{
    return $GLOBALS['extension_loaded'];
}

// Used to manually set the value returned by the mocked extension_loaded()
$extension_loaded = true;

// phpcs:enable
