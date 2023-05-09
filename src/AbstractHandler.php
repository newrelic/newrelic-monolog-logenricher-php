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

use CurlHandle;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\Curl;
use Monolog\Handler\MissingExtensionException;
use Monolog\Level;

abstract class AbstractHandler extends AbstractProcessingHandler
{
    protected ?string $host = null;
    protected string $endpoint = 'log/v1';
    protected string $licenseKey;
    protected string $protocol = 'https://';

    /**
     * @param Level $level The minimum logging level to trigger handler
     * @param bool $bubble Whether messages should bubble up the stack.
     *
     * @throws MissingExtensionException If the curl extension is missing
     */
    public function __construct(
        Level $level = Level::Debug,
        bool $bubble = true
    ) {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException(
                'The curl extension is required to use this Handler'
            );
        }

        $this->licenseKey = ini_get('newrelic.license');
        if (!$this->licenseKey) {
            $this->licenseKey = "NO_LICENSE_KEY_FOUND";
        }

        parent::__construct($level, $bubble);
        $this->level = $level;
    }

    /**
     * Sets the New Relic license key. Defaults to the New Relic INI's
     * value for 'newrelic.license' if available.
     *
     * @param string $key
     */
    public function setLicenseKey(mixed $key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Only string key allowed');
        }
        $this->licenseKey = $key;
    }

    /**
     * Sets the hostname of the New Relic Logging API. Defaults to the
     * US Prod endpoint (log-api.newrelic.com). Another useful value is
     * log-api.eu.newrelic.com for the EU production endpoint.
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
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

        $headers = array(
            'Content-Type: application/json',
            'X-License-Key: ' . $this->licenseKey
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        Curl\Util::execute($ch, 5, false);
    }

    /**
     * Obtains a curl handler initialized to POST to the host specified by
     * $this->setHost()
     *
     * @return  resource    $ch             curl handler
     */
    protected function getCurlHandler(): CurlHandle
    {
        $host = is_null($this->host)
            ? self::getDefaultHost($this->licenseKey)
            : $this->host;

        $url = "{$this->protocol}{$host}/{$this->endpoint}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return $ch;
    }

    /**
     * Given a licence key, returns the default log API host for that region.
     *
     * @param string $licenseKey
     * @return string
     */
    protected static function getDefaultHost($licenseKey)
    {
        if (!is_string($licenseKey)) {
            throw new \InvalidArgumentException(
                'Unknown license key of type ' . gettype($licenseKey)
            );
        }

        $matches = array();
        if (preg_match('/^([a-z]{2,3})[0-9]{2}x/', $licenseKey, $matches)) {
            $region = ".{$matches[1]}";
        } else {
            // US licence keys generally don't include region identifiers, so
            // we'll default to that.
            $region = '';
        }

        return "log-api$region.newrelic.com";
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
