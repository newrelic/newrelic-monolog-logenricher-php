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

use Monolog\Handler\Curl;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Util;

class Handler extends AbstractProcessingHandler
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
        if (false == $this->licenseKey) {
            $this->licenseKey = "NO_LICENSE_KEY_FOUND";
        }

        parent::__construct($level, $bubble);
    }

    public function setLicenseKey($key)
    {
        $this->licenseKey = $key;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    private function getCurlHandler()
    {
        $url = sprintf("https://%s/%s", $this->host, $this->endpoint);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return $ch;
    }

    protected function write(array $record)
    {
        $this->send($record["formatted"]);
    }

    public function handleBatch(array $records)
    {
        $level = $this->level;
        $records = array_filter($records, function ($record) use ($level) {
            return ($record['level'] >= $level);
        });
        if ($records) {
            $this->sendBatch($this->getFormatter()->formatBatch($records));
        }
    }

    protected function sendBatch($data)
    {
        $ch = $this->getCurlHandler();

        $headers = ['Content-Type: application/json',
                    'X-License-Key: ' . $this->licenseKey];

        $postData = '[{"logs":' . $data . '}]';

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        Curl\Util::execute($ch, 5, false);
    }

    protected function send($data)
    {
        $ch = $this->getCurlHandler();

        $headers = ['Content-Type: application/json',
                    'X-License-Key: ' . $this->licenseKey];

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        Curl\Util::execute($ch, 5, false);
    }

    public function setFormatter(FormatterInterface $formatter)
    {
        if ($formatter instanceof Formatter) {
            return parent::setFormatter($formatter);
        }
        $fq = 'NewRelic\Monolog\Enricher\Handler'
        throw new \InvalidArgumentException(
            $fq . ' is only compatible with ' . $fq
        );
    }

    protected function getDefaultFormatter()
    {
        return new Formatter(Formatter::BATCH_MODE_JSON, false);
    }
}
