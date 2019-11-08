<?php

namespace NewRelic\Monolog\Enricher;

use PHPUnit_Framework_TestCase;

// A top secret PHP trick: you can't mock global functions like
// newrelic_get_linking_metadata() using PHPUnit's built in mocking support,
// because that can only handle classes, but you can use PHP's name resolution
// rules to get the same effect.
//
// If you have a function call xyz() within a namespace Foo\Bar, PHP will try
// to look up the function in this way:
// 1. Foo\Bar\xyz()
// 2. \xyz()
//
// Therefore, if you define the function within the same namespace, you can
// override the function that would otherwise be called.
function newrelic_get_linking_metadata()
{
    return array();
}

class ProcessorTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        // TODO: extend this so it tests the eventual behaviour of
        // Processor::__invoke().
        $input = array('foo' => 'bar');

        $proc = new Processor();
        $this->assertSame($input, $proc($input));
    }
}
