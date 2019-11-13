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

use PHPUnit_Framework_TestCase;

/**
 * Override the New Relic PHP Extension's `newrelic_get_linking_metadata()`
 * function to mock a response
 *
 * @return array
 */
function newrelic_get_linking_metadata()
{
    return array('hostname' => 'example.host',
                 'entity.name' => 'Processor Tests',
                 'entity.type' => 'SERVICE');
}

class ProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * getMockedProcessor returns a mocked NewRelic\Monolog\Enricher\Processor
     * that is configured to return a set value in
     * `Processor::contextAvailable`. This allows testing scenarios where a
     * compatible New Relic extension (v9.3 or higher) is not available.
     *
     * @param bool $nr_ext_compat Whether a compatible extension was 'found'
     * @return MockedProcessor
     */
    private function getMockedProcessor($nr_ext_compat)
    {
        $proc = $this->getMockBuilder('NewRelic\Monolog\Enricher\Processor')
                     ->setMethods(array('contextAvailable'))
                     ->getMock();
        $proc->method('contextAvailable')
             ->willReturn($nr_ext_compat);

        return $proc;
    }

    /**
     * Tests that the array returned by `newrelic_get_linking_metadata()`
     * is inserted at `$logRecord['extra']['newrelic-context'] when a
     * compatible New Relic extension is loaded
     */
    public function testInvoke()
    {
        $input = array('foo' => 'bar');

        $proc = $this->getMockedProcessor(true);
        $enriched_record = $proc($input);

        $expected = newrelic_get_linking_metadata();
        $got = $enriched_record['extra']['newrelic-context'];
        $this->assertSame($expected, $got);
    }

    /**
     * Tests that the given Monolog record is returned unchanged when a
     * compatible New Relic extension is not loaded
     */
    public function testInputPassthroughWhenNewRelicNotLoaded()
    {
        $input = array('foo' => 'bar');
        $proc = $this->getMockedProcessor(false);

        $this->assertSame($input, $proc($input));
    }

    /**
     * Tests that the given Monolog record is returned unchanged when a
     * compatible New Relic extension is not loaded
     */
    public function testContextAvailbleMatchesFnExistsCall()
    {
        $proc = new Processor();
        $exists = function_exists('newrelic_get_linking_metadata');
        $this->assertSame($exists, $proc->contextAvailable());
    }
}
