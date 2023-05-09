<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the Formatter class for the New Relic Monolog Enricher
 * on Monolog API v2
 *
 * This class formats a Monolog record as a JSON object with a compatible
 * timestamp, and any New Relic context information moved to the top-level.
 * The resulting output is intended to be sent to New Relic Logs via a
 * compatible log forwarder with New Relic plugin installed (see this
 * project's README for links to available plugins).
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher;

use Monolog\LogRecord;
use stdClass;

/**
 * Formats record as a JSON object with transformations necessary for
 * ingestion by New Relic Logs
 */
class Formatter extends AbstractFormatter
{
    /**
     * Normalizes each record individually before JSON encoding the complete
     * batch of records as a JSON array.
     *
     * @param LogRecord[] $records
     * @return string
     */
    protected function formatBatchJson(array $records): string
    {
        foreach ($records as $key => $record) {
            $normalized = $this->normalize($record->toArray());

            // Adhere to format of Monolog 2.x JSON format
            if (
                isset($normalized['context'])
                && $normalized['context'] === []
            ) {
                $normalized['context'] = new stdClass();
            }
            if (
                isset($normalized['extra'])
                && $normalized['extra'] === []
            ) {
                $normalized['extra'] = new stdClass();
            }

            $records[$key] = $normalized;
        }
        return $this->toJson($records, true);
    }
}
