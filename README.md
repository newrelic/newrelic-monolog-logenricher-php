# Monolog components to enable New Relic logs

## Development

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
