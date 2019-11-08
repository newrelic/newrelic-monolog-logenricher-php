<?php

namespace NewRelic\Monolog\Enricher;

use Monolog\Processor\ProcessorInterface;

class Processor implements ProcessorInterface
{
    public function __invoke(array $records)
    {
        // TODO: add the entity metadata to
        // $records['extra']['newrelic-context'].

        return $records;
    }
}
