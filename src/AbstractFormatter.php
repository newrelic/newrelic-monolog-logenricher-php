<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the abstract parent of the Formatter class for
 * the New Relic Monolog Enricher. This class implements all functionality
 * that is compatible with all Monolog API versions
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
abstract class AbstractFormatter extends JsonFormatter
{
    /**
     * @param int $batchMode
     * @param bool $appendNewline
     */
    public function __construct(
        $batchMode = self::BATCH_MODE_NEWLINES,
        $appendNewline = true
    ) {
        // BATCH_MODE_NEWLINES is required for batch compatibility with New
        // Relic log forwarder plugins, which handle batching records. When
        // using the New Relic Monolog handler along side a batching handler
        // such as the BufferHandler, BATCH_MODE_JSON is required to adhere
        // to the New Relic logs API bulk ingest format.
        parent::__construct($batchMode, $appendNewline);
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
