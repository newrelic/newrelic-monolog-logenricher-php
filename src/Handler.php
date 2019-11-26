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
use Monolog\Util;

class Handler extends AbstractProcessingHandler
{
    protected const HOST = 'staging-log-api.newrelic.com';
    protected const ENDPOINT = 'log/v1';
    protected $licenseKey;

    /**
     * @param string|int $level  The minimum logging level to trigger this handler
     * @param bool       $bubble Whether or not messages that are handled should bubble up the stack.
     *
     * @throws MissingExtensionException If the curl extension is missing
     */
    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is needed to use the LogglyHandler');
        }

        // TODO check value for false
        $this->licenseKey = ini_get('newrelic.license');

        parent::__construct($level, $bubble);
    }

    private function getCurlHandler()
    {
        $url = sprintf("https://%s/%s", static::HOST, static::ENDPOINT);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return $ch;
    }

    protected function write(array $record): void
    {
        $this->send($this->getFormatter()->format($record));
    }

    // TODO Not quite supported yet.  Can alter the Formatter to allow
    // BATCH_MODE_JSON and adhere to the NR Log api for batching
    public function handleBatch(array $records): void
    {
        $level = $this->level;
        $records = array_filter($records, function ($record) use ($level) {
            return ($record['level'] >= $level);
        });
        if ($records) {
            $this->send($this->getFormatter()->formatBatch($records));
        }
    }

    protected function send(string $data): void
    {
        $ch = $this->getCurlHandler();

        $headers = ['Content-Type: application/json',
                    'X-License-Key: ' . $this->licenseKey];

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        Curl\Util::execute($ch, 5, false);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new Formatter();
    }
}
