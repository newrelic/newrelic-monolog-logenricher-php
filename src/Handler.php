<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the abstract parent of the Handler class for
 * the New Relic Monolog Enricher. This class implements all functionality
 * that is compatible with all Monolog API versions
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher;

use Monolog\Logger;

// phpcs:disable
/*
 * This extension to the Monolog framework supports the same PHP versions
 * as the New Relic PHP Agent (>=5.3).  Older versions of PHP are only
 * compatible with Monolog v1, therefore, To accomodate Monolog v2's explicit
 * and required type annotations, some overridden methods must be implemented
 * both with compatible annotations for v2 and without for v1
 */
if (Logger::API == 2) {
    require_once dirname(__FILE__) . '/api2/Handler.php';
} else {
    require_once dirname(__FILE__) . '/api1/Handler.php';
}
// phpcs:enable
