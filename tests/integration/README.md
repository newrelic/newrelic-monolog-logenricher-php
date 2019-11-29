# Integration tests

These tests stand up and use a full `Logger` instance from either Monolog 1 or
2 with `NewRelic\Monolog\Enricher\Handler` configured, and verify that the
handler is sending data in the format expected by the
[New Relic Log API](https://docs.newrelic.com/docs/logs/new-relic-logs/log-api/introduction-log-api).

Note that, although this project in general supports PHP 5.3, the integration
tests require PHP 5.4.

## Running the tests

Normally, you would use the top level `composer integration` command. However,
you can run these tests independently if you choose.

First, you will need to install the dependencies for the test server by running
`composer install` in the `server` directory.

Then you'll need to install the dependencies for whichever integration test
suite you want to run. `v1` contains the tests for Monolog 1; `v2` the tests
for Monolog 2. Again, `composer install` in the relevant directory will handle
that.

Finally, you can run PHPUnit as per normal within the test suite of interest,
using `./vendor/bin/phpunit`.

## Theory of operation

Rather than hitting the New Relic Log API endpoint, these tests stand up a test
HTTP server and configure the handler to use that.

The server can be found in the `server` directory. It's a basic
[`react/http`](https://github.com/reactphp/http) based server, and it exposes
two endpoints:

* `POST /log/v1`: an endpoint that emulates the real Log API endpoint.
* `GET /last`: returns the request body and headers that were sent to the
  emulated endpoint as JSON.

That's all it does: it's a simple parrot.

## Test organisation

The versioned test suites share almost all of their code, which is kept in
`common`. The handler test case is identical across the two versions, since the
Monolog API is essentially unchanged across versions.
