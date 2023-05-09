<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains an insecure variant of the normal handler.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher\IntegrationTest;

use NewRelic\Monolog\Enricher\Handler;

/**
 * A handler that specifically sets the protocol to HTTP instead of HTTPS.
 *
 * Obviously this is a terrible idea, so you shouldn't use this in practice.
 * (Particularly since the Log API doesn't actually allow it.)
 */
class InsecureHandler extends Handler
{
    protected string $protocol = 'http://';
}
