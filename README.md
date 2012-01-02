# FUnit

A simple test suite for PHP 5.3+, partially inspired by [QUnit](http://docs.jquery.com/QUnit).

## Features

* Simple to write tests and get output
* Short, straightforward syntax
* Designed to be run in the CLI

## Example

	<?php
	use \FUnit\fu;
	require __DIR__ . '/FUnit.php';

	fu::test("this is a test", function() {
		fu::ok(1, "the integer '1' is okay");
		fu::ok(0, "the integer '0' is not okay"); // this will fail!
	});

	fu::run();

Will output:

	Running test 'this is a test...'
	RESULTS:
	--------------------------------------------
	TEST: this is a test (1/2):
	 * PASS ok(1) the integer '1' is okay
	 * FAIL ok(0) the integer '0' is not okay

	TOTAL ASSERTIONS: 1 pass, 1 fail, 2 total
	TESTS: 1 run, 0 pass, 1 total

See the `example.php` file for more.


## Methods

* `fu::test($name, \Closure $test)`

  Add a test with the name $name and an anonymous function $test. $test would contain various **assertions**, like `fu::ok()`

* `fu::ok($a, $msg = null)`

  Assert that $a is truthy. Optional $msg describes the test

* `fu::equal($a, $b, $msg = null)`

  Assert that $a == $b. Optional $msg describes the test

* `fu::not_equal($a, $b, $msg = null)`

  Assert that $a != $b. Optional $msg describes the test

* `fu::strict_equal($a, $b, $msg = null)`

  Assert that $a === $b. Optional $msg describes the test

* `fu::deep_not_equal($a, $b, $msg = null)`

  Assert that $a !== $b. Optional $msg describes the test

* `fu::setup(\Closure $setup)`

  Register a function to run at the start of each test. See `fu::fixture()`

* `fu::teardown(\Closure $setup)`

  Register a function to run at the end of each test. See `fu::fixture()` and `fu::reset_fixtures()`

* `fu::fixture($key, [$val])`

  Retrieve or register a fixture. Use this in fu::setup() to assign fixtures to keys, and retrieve those fixtures in your tests

* `fu::reset_fixtures()`

  Clears out all fixtures in the fu::$fixtures array. This doesn't guarantee clean shutdown/close

* `fu::run($report = true)`

  Runs the registered tests. If `false` is passed, the report output is suppressed