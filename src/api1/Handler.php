<?php

/**
 * Copyright [2019] New Relic Corporation. All rights reserved.
 * SPDX-License-Identifier: Apache-2.0
 *
 * This file contains the Handler class for Monolog API v1
 *
 * This class provides a largely self-contained solution for
 * getting started with New Relic logging, including support
 * for batching uploads.
 *
 * @author New Relic PHP <php-agent@newrelic.com>
 */

namespace NewRelic\Monolog\Enricher;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\Curl;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Util;

if (Logger::API == 1) {
    class Handler extends AbstractHandler
    {
        /**
         * Delegates upload of single record to send()
         *
         * @param array $record
         */
        protected function write(array $record)
        {
            $this->send($record["formatted"]);
        }

        /**
         * Iterates over batched data, filtering out logs with levels lower
         * than the constructed threshold. If applicable logs are found, they
         * are formatted as a JSON array compatible with New Relic's batch Log
         * ingest and delegated to sendBatch()
         *
         * @param array $record
         */
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


        /**
         * Sets Handler's Formatter. Note: only
         * NewRelic\Monolog\Enricher\Formatter is compatible.
         *
         * @param FormatterInterface $formatter
         *
         * @throws InvalidArgumentException If incompatible Formatter given
         */
        public function setFormatter(FormatterInterface $formatter)
        {
            if ($formatter instanceof Formatter) {
                return parent::setFormatter($formatter);
            }
            throw new \InvalidArgumentException(
                'NewRelic\Monolog\Enricher\Handler is only compatible with '
                . 'NewRelic\Monolog\Enricher\Formatter'
            );
        }

    
        /**
         * Returns a New Relic Formatter initialized with API compatible values
         *
         * @return Formatter
         */
        protected function getDefaultFormatter()
        {
            return new Formatter(Formatter::BATCH_MODE_JSON, false);
        }
    }
}
