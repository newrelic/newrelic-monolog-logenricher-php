<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the tests for the New Relic Monolog Enricher
 * JSON Formatter.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher;

use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class FormatterTest extends PHPUnit_Framework_TestCase
{

    /**
     * Generates a Monolog record that optionally contains New Relic
     * context information (enabled by default)
     *
     * @param bool $withNrContext
     * @return array
     */
    private function getRecord($withNrContext)
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

        if ($withNrContext) {
            $nr_context = array(
                'hostname' => 'example.host',
                'entity.name' => 'Processor Tests',
                'entity.type' => 'SERVICE',
                'trace.id' => 'aabb1234AABB4321',
                'span.id' => 'wxyz9876WXYZ6789'
            );
            $record['extra']['newrelic-context'] = $nr_context;
        }

        return $record;
    }

    /**
     * Generates the expected string for a given record after formatting.
     * Optionally appends a trailing newline (enabled by default)
     *
     * @param array $record
     * @param bool $appendNewline
     * @return array
     */
    private function getExpectedForRecord($record, $appendNewline = true)
    {
        $expected =
        '{"message":"test",'
        . '"context":' . (Logger::API == 1 ? '[]' : '{}') . ','
        . '"level":300,"level_name":"WARNING","channel":"test",'
        . '"extra":' . (Logger::API == 1 ? '[]' : '{}') . ','
        . '"datetime":' . json_encode($record['datetime']) . ',';

        if (isset($record['extra']['newrelic-context'])) {
            $expected = $expected . '"hostname":"example.host",'
                . '"entity.name":"Processor Tests","entity.type":"SERVICE",'
                . '"trace.id":"aabb1234AABB4321","span.id":"wxyz9876WXYZ6789",';
        }

        $expected = $expected . '"timestamp":'
            . intval($record['datetime']->format('U.u') * 1000) . '}'
            . ($appendNewline ? "\n" : '');

        return $expected;
    }

    /**
     * Verifies constructor sets expected parameters and respects overrides
     */
    public function testConstruct()
    {
        // Verify default parameters
        $formatter = new Formatter();
        $this->assertEquals(
            Formatter::BATCH_MODE_NEWLINES,
            $formatter->getBatchMode()
        );
        $this->assertEquals(true, $formatter->isAppendingNewlines());

        // Verify that batch mode can be set, and trailing newlines can
        // be disabled
        $formatter = new Formatter(Formatter::BATCH_MODE_JSON, false);
        $this->assertEquals(
            Formatter::BATCH_MODE_JSON,
            $formatter->getBatchMode()
        );
        $this->assertEquals(false, $formatter->isAppendingNewlines());
    }

    /**
     * Tests format which in turn calls overridden normalize method containing
     * the New Relic transformations
     */
    public function testFormat()
    {
        // Test with trailing newline
        $formatter = new Formatter();
        $record = $this->getRecord(true);
        $this->assertEquals(
            $this->getExpectedForRecord($record),
            $formatter->format($record)
        );

        // Test without trailing newline
        $formatter = new Formatter(Formatter::BATCH_MODE_NEWLINES, false);
        $this->assertEquals(
            $this->getExpectedForRecord($record, false),
            $formatter->format($record)
        );

        // Test without New Relic context information
        $formatter = new Formatter();
        $record = $this->getRecord(false);
        $this->assertEquals(
            $this->getExpectedForRecord($record),
            $formatter->format($record)
        );
    }

    /**
     * Tests that batch records are processed correctly according to
     * $batchMode parameter
     */
    public function testFormatBatch()
    {
        $formatter = new Formatter(Formatter::BATCH_MODE_JSON, false);
        // One record with New Relic context information, one without
        $records = array(
            $this->getRecord(true),
            $this->getRecord(false),
        );

        $this->assertEquals(
            // Test records when batch processed as a JSON array.
            '[' . $this->getExpectedForRecord($records[0], false) . ','
            . $this->getExpectedForRecord($records[1], false) . ']',
            $formatter->formatBatch($records)
        );


        $formatter = new Formatter();
        // One record with New Relic context information, one without
        $records = array(
            $this->getRecord(true),
            $this->getRecord(false),
        );

        $this->assertEquals(
            // Separate entries by newline, however do not append final newline
            // to match Monolog\JsonFormatter::formatBatchNewlines behavior
            $this->getExpectedForRecord($records[0])
            . $this->getExpectedForRecord($records[1], false),
            $formatter->formatBatch($records)
        );
    }
}
