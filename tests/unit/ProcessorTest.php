<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the tests for the New Relic Monolog Enricher
 * Processor. Tests must cover cases when a compatible New Relic
 * extension (v9.3 or higher) is not available
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher;

use DateTimeImmutable;
use DateTimeZone;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * Tests that the array returned by `newrelic_get_linking_metadata()`
     * is inserted at `$logRecord['extra']['newrelic-context'] when a
     * compatible New Relic extension is loaded
     */
    public function testInvoke()
    {
        $record = new LogRecord(
            new DateTimeImmutable("now", new DateTimeZone("UTC")),
            'test',
            Level::Warning,
            'test',
            [],
            [],
        );

        $proc = $this->getMockedProcessor(true);
        $enriched_record = $proc($record);

        $expected = newrelic_get_linking_metadata();
        $got = $enriched_record['extra']['newrelic-context'];
        $this->assertSame($expected, $got);
    }

    /**
     * getMockedProcessor returns a mocked NewRelic\Monolog\Enricher\Processor
     * that is configured to return a set value in
     * `Processor::contextAvailable`. This allows testing scenarios where a
     * compatible New Relic extension (v9.3 or higher) is not available.
     *
     * @param bool $nr_ext_compat Whether a compatible extension was 'found'
     * @return Processor
     */
    private function getMockedProcessor(bool $nr_ext_compat): Processor
    {
        return new class ($nr_ext_compat) extends Processor {
            public function __construct(
                private readonly bool $newRelicExtensionAvailable
            ) {
            }

            protected function contextAvailable(): bool
            {
                return $this->newRelicExtensionAvailable;
            }
        };
    }

    /**
     * Tests that the given Monolog record is returned unchanged when a
     * compatible New Relic extension is not loaded
     */
    public function testInputPassThroughWhenNewRelicNotLoaded()
    {
        $record = new LogRecord(
            new DateTimeImmutable("now", new DateTimeZone("UTC")),
            'test',
            Level::Warning,
            'test',
            [],
            [],
        );
        $proc = $this->getMockedProcessor(false);

        $this->assertSame($record, $proc($record));
    }
}

function newrelic_get_linking_metadata(): array
{
    return array(
        'hostname' => 'example.host',
        'entity.name' => 'Processor Tests',
        'entity.type' => 'SERVICE'
    );
}
