# Monolog components to enable New Relic logs

This package provides the components required to integrate a PHP application
using [Monolog](https://github.com/Seldaek/monolog) with
[New Relic Logs](https://newrelic.com/products/logs).

## Contents

* [Components](#components)
* [Requirements](#requirements)
* [Installation](#installation)
* [Examples](#examples)
  * [Sending data directly to New Relic Logs](#sending-data-directly-to-new-relic-logs)
  * [Selectively sending log records](#selectively-sending-log-records)
  * [Buffering log records to improve performance](#buffering-log-records-to-improve-performance)
  * [Manually specifying the license key and/or host](#manually-specifying-the-license-key-andor-host)
  * [Integrating with an existing logging tool](#integrating-with-an-existing-logging-tool)
* [Development](#development)
  * [Setting up a development environment](#setting-up-a-development-environment)
  * [Coding standards](#coding-standards)
  * [Running unit tests](#running-unit-tests)
  * [Running integration tests](#running-integration-tests)

## Components

Three components are provided:

1. A
   [`Handler`](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#core-concepts),
   which delivers log records directly from Monolog to New Relic Logs.

2. A
   [`Processor`](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#using-processors).
   This can be used in conjunction with the
   [New Relic PHP agent](https://docs.newrelic.com/docs/agents/php-agent) to
   decorate log records with
   [linking metadata](https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelicgetlinkingmetadata),
   which allows you use
   [logs-in-context](https://docs.newrelic.com/docs/logs/new-relic-logs/enable-logs-context/enable-logs-context-apm-agents)
   to correlate log records with related data across the New Relic platform.

3. A
   [`Formatter`](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#customizing-the-log-format),
   which extends the `JsonFormatter` provided by Monolog to take the decorated
   log records from the `Processor` and output them with the
   [simplified JSON body structure expected by New Relic Logs](https://docs.newrelic.com/docs/logs/new-relic-logs/log-api/introduction-log-api#json-content).

Please see the [examples](#examples) section below for more detail on how to
integrate these components with your application.

## Requirements

* [Monolog](https://github.com/Seldaek/monolog), version 1 or 2.
* PHP 5.3.0 or later, although a
  [currently supported version of PHP](https://php.net/supported-versions.php)
  is strongly recommended.

To use the `Handler`, you will also need the following:

* PHP's [curl extension](https://php.net/curl).

To enable
[logs-in-context](https://docs.newrelic.com/docs/logs/new-relic-logs/enable-logs-context/enable-logs-context-apm-agents)
functionality, you will also need:

* The [New Relic PHP agent](https://docs.newrelic.com/docs/agents/php-agent),
  version 9.3 or later.

## Installation

This package is available
[on Packagist](https://packagist.org/packages/newrelic/monolog-enricher), and
should be installed using [Composer](https://getcomposer.org):

```bash
composer require newrelic/monolog-logenricher
```

## Examples

### Sending logs directly to New Relic Logs

The simplest way to use this package is to use the `Processor` and `Handler` to
send data directly to New Relic Logs:

```php
<?php

use Monolog\Logger;
use NewRelic\Monolog\Enricher\{Handler, Processor};

$log = new Logger('log');
$log->pushProcessor(new Processor);
$log->pushHandler(new Handler);

$log->info('Hello, world!');
```

If the [New Relic PHP agent](https://docs.newrelic.com/docs/agents/php-agent)
is installed, then the `Handler` should be able to detect the
[license key](https://docs.newrelic.com/docs/accounts/install-new-relic/account-setup/license-key)
from the PHP agent, and the `Processor` will automatically add linking metadata
to log records.

If you do not use New Relic APM, you can skip pushing the processor: the
`Handler` can operate independently.

### Selectively sending log records

By default, using the `Handler` means that each log record will cause a HTTP
request to occur to the New Relic Logs API. This may add overhead to your
application if a significant number of records are logged.

Like most built-in Monolog handlers, the `Handler` class allows the
specification of a minimum
[log level](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#log-levels):
log records below the given level will not be sent to New Relic. Therefore, if
you don't want to see logs below `WARNING`, you could change the instantiation
of `Handler` as follows:

```php
<?php
// ...

$log->pushHandler(new Handler(Logger::WARNING));
```

### Buffering log records to improve performance

Another way of avoiding a HTTP request for each log message that is sent is to
use Monolog's built-in
[`BufferHandler`](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/BufferHandler.php)
to batch log messages, and then send them in one message at the end of the
request:

```php
<?php

use Monolog\Handler\BufferHandler;
use Monolog\Logger;
use NewRelic\Monolog\Enricher\{Handler, Processor};

$log = new Logger('log');
$log->pushProcessor(new Processor);
$log->pushHandler(new BufferHandler(new Handler));

$log->info('Hello, world!');
```

### Manually specifying the license key and/or host

The `Handler` class provides methods to set the license key or the New Relic
Log API host that will be used. This may be useful if the New Relic PHP agent
is not installed, or if you wish to log to a different account or region.

```php
<?php
// ...

$handler = new Handler;
$handler->setHost('log-api.eu.newrelic.com');
$handler->setLicenseKey('0123456789abcdef0123456789abcdef01234567');
$log->pushHandler($handler);
```

### Integrating with an existing logging tool

If you have a logging tool already configured to send logs to New Relic Logs
(such as Fluentd, AWS CloudWatch, or
[another logging tool supported by New Relic Logs](https://docs.newrelic.com/docs/logs/new-relic-logs/enable-logs/enable-new-relic-logs#enable-logs)),
then you may prefer to use the `Processor` and `Formatter` to send logs to that
tool, rather than sending logs directly to New Relic using the `Handler`.

For example, if your logging tool is configured to pick up
[NDJSON](http://ndjson.org/) on `stderr`, you could configure the logger as
follows:

```php
<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use NewRelic\Monolog\Enricher\{Handler, Processor};

$log = new Logger('log');
$log->pushProcessor(new Processor);

$handler = new StreamHandler('php://stderr');
$handler->setFormatter(new Formatter);
$log->pushHandler($handler);

$log->info('Hello, world!');
```

More information on configuring your logging tool to send logs to New Relic
Logs can be found
[within the New Relic documentation](https://docs.newrelic.com/docs/logs/new-relic-logs/enable-logs/enable-new-relic-logs).

## Development

If you would like to contribute to this project, please read the
[CONTRIBUTING.md](CONTRIBUTING.md) and [code of conduct](CODE_OF_CONDUCT.md),
along with the instructions below on our coding standards and running the test
suites.

### Setting up a development environment

The development dependencies for this project are managed by Composer, and
should be installed via Composer:

```bash
composer install
```

### Coding standards

This project conforms to [PSR-12](https://www.php-fig.org/psr/psr-12/), with a
soft line length limit of 80 characters.

[PHP\_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) is used to
ensure conformity. You can run `phpcs` to check the current code via the
following Composer script:

```bash
composer coding-standard-check
```

Alternatively, you can use `phpcbf` to automatically fix most errors:

```bash
composer coding-standard-fix
```

### Running unit tests

This project uses [PHPUnit 4](https://phpunit.de/manual/4.8/en/index.html),
which is the last PHPUnit version to support PHP 5.3.

You can run the test suite via Composer:

```bash
composer test
```

If `phpdbg` is available, you can also generate a code coverage report while
running the test suite:

```bash
composer test-coverage
```

This will write a HTML coverage report to `coverage/index.html`.

### Running integration tests

It's also possible to run a suite of integration tests against both Monolog 1
and 2 via Composer:

```bash
composer integration
```

More information on these tests is available in the
[`tests/integration` README](tests/integration/README.md).
