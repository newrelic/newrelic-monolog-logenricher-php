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

use Monolog\Handler\Curl;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Util;

abstract class AbstractHandler extends AbstractProcessingHandler
{
    protected $host = 'log-api.newrelic.com';
    protected $endpoint = 'log/v1';
    protected $licenseKey;

    /**
     * @param string|int $level  The minimum logging level to trigger handler
     * @param bool       $bubble Whether messages should bubble up the stack.
     *
     * @throws MissingExtensionException If the curl extension is missing
     */
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException(
                'The curl extension is needed to use the LogglyHandler'
            );
        }

        $this->licenseKey = ini_get('newrelic.license');
        if (!$this->licenseKey) {
            $this->licenseKey = "NO_LICENSE_KEY_FOUND";
        }

        parent::__construct($level, $bubble);
    }

    /**
     * Sets the New Relic license key. Defaults to the New Relic INI's
     * value for 'newrelic.license' if available.
     *
     * @param  string    $host
     */
    public function setLicenseKey($key)
    {
        $this->licenseKey = $key;
    }

    /**
     * Sets the hostname of the New Relic Logging API. Defaults to the
     * US Prod endpoint (log-api.newrelic.com). Another useful value is
     * log-api.eu.newrelic.com for the EU production endpoint.
     *
     * @param  string    $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Obtains a curl handler initialized to POST to the host specified by
     * $this->setHost()
     *
     * @return  resource    $ch             curl handler
     */
    private function getCurlHandler()
    {
        $url = sprintf("https://%s/%s", $this->host, $this->endpoint);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return $ch;
    }

    /**
     * Augments JSON-formatted data with New Relic license key and other
     * necessary headers, and POSTs the log to the New Relic logging
     * endpoint via Curl
     *
     * @param string $data
     */
    protected function send($data)
    {
        $ch = $this->getCurlHandler();

        $headers = array('Content-Type: application/json',
                    'X-License-Key: ' . $this->licenseKey);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        Curl\Util::execute($ch, 5, false);
    }

    /**
     * Augments a JSON-formatted array data with New Relic license key
     * and other necessary headers, and POSTs the log to the New Relic
     * logging endpoint via Curl
     *
     * @param string $data
     */
    protected function sendBatch($data)
    {
        $ch = $this->getCurlHandler();

        $headers = array(
            'Content-Type: application/json',
            'X-License-Key: ' . $this->licenseKey
        );

        $postData = '[{"logs":' . $data . '}]';

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        Curl\Util::execute($ch, 5, false);
    }
}

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
