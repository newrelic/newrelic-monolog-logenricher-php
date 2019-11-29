<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains a test verifying that the test suite is being run against
 * the correct version of Monolog.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher\IntegrationTest\V2;

use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class ApiTest extends PHPUnit_Framework_TestCase
{
    public function testApiVersion()
    {
        $this->assertSame(2, Logger::API);
    }
}
