<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the Formatter class for the New Relic Monolog Enricher
 * on Monolog API v1
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

use Monolog\Logger;

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
     * @param array $records
     * @return string
     */
    protected function formatBatchJson(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->normalize($record);
        }
        return $this->toJson($records, true);
    }
}
