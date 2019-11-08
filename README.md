# Monolog components to enable New Relic logs

## Development

### Setting up a development environment

The development dependencies for this project are managed by Composer, and
should be installed via Composer:

```bash
composer install
```

### Running tests

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
