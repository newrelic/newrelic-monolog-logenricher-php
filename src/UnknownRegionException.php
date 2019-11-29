<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the exception thrown when an unknown region identifer is
 * found.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher;

use RuntimeException;

/**
 * An exception thrown if an unknown region identifier is found in a licence
 * key.
 */
class UnknownRegionException extends RuntimeException
{
    public function __construct($region)
    {
        parent::__construct('Unknown New Relic region found in license key; '
            . 'cannot automatically detect Log API host (check your license '
            . "key, and use the setHost() method if necessary): $region");
    }
}
