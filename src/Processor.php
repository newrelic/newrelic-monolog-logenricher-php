<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the Processor class for the New Relic Monolog Enricher.
 * When invoked, the class adds contextual metadata to a Monolog record that
 * links the log to the current New Relic application.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher;

use Monolog\Processor\ProcessorInterface;

/**
 * Adds metadata to log that associates it with current New Relic application
 */
class Processor implements ProcessorInterface
{
    /**
     * Returns the given record with the New Relic linking metadata added
     * if a compatible New Relic extension is loaded, otherwise returns the
     * given record unmodified
     *
     * @param  array $record A Monolog record
     * @return array Given record, with New Relic metadata added if available
     */
    public function __invoke(array $record)
    {
        if ($this->contextAvailable()) {
            $linking_data = newrelic_get_linking_metadata();
            $record['extra']['newrelic-context'] = $linking_data;
        }
        return $record;
    }

    /**
     * Checks if a compatible New Relic extension (v9.3 or higher) is loaded
     *
     * @return bool
     */
    protected function contextAvailable()
    {
        return function_exists('newrelic_get_linking_metadata');
    }
}
