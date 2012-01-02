<?php
use \FUnit\fu;

require __DIR__ . '/FUnit.php';

fu::setup(function() {
	putenv('FOOBAR=baz');
});

fu::teardown(function() {
	putenv('FOOBAR=');
});

fu::test("this is a test", function() {
	fu::ok(1, "the integer '1' is okay");
	fu::ok(0, "the integer '0' is not okay"); // this will fail!
});

fu::test("another test", function() {
	fu::equal(true, 1, "the integer '1' is truthy");
	fu::not_strict_equal(true, 1, "the integer '1' is NOT true");
	fu::equal(getenv('FOOBAR'), 'baz', "the env var 'foobar' should be 'baz' from fu::setup()");
});

fu::run();
