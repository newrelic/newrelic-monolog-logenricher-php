<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the Formatter class for the New Relic Monolog Enricher.
 * This class formats a Monolog record as a JSON object with a compatible
 * timestamp, and any New Relic context information moved to the top-level.
 * The resulting output is intended to be sent to New Relic Logs via a
 * compatible log forwarder with New Relic plugin installed (see this
 * project's README for links to available plugins).
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher;

use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;

/**
 * Formats record as a JSON object with transformations necessary for
 * ingestion by New Relic Logs
 */
class Formatter extends JsonFormatter
{
    /**
     * @param bool $appendNewline
     */
    public function __construct($appendNewline = true)
    {
        // BATCH_MODE_NEWLINES is required for compatibility with New Relic
        // log forwarder plugins, which handle batching records in accordance
        // with the New Relic Logging API
        parent::__construct(self::BATCH_MODE_NEWLINES, $appendNewline);
    }


    /**
     * Moves New Relic context information from the
     * `$data['extra']['newrelic-context']` array to top level of record,
     * converts `datetime` object to `timestamp` top level element represented
     * as milliseconds since the UNIX epoch, and finally, normalizes the data
     *
     * @param mixed $data
     * @param int $depth
     * @return mixed
     */
    protected function normalize($data, $depth = 0)
    {
        if ($depth == 0) {
            if (isset($data['extra']['newrelic-context'])) {
                $data = array_merge($data, $data['extra']['newrelic-context']);
                unset($data['extra']['newrelic-context']);
            }
            $data['timestamp'] = intval(
                $data['datetime']->format('U.u') * 1000
            );
        }
        return parent::normalize($data, $depth);
    }
}
