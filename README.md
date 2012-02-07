# FUnit

A simple test suite for PHP 5.3+, partially inspired by [QUnit](http://docs.jquery.com/QUnit).

## Features

* Simple to write tests and get output – start writing tests **fast**
* Short, straightforward syntax
* Can be run from the CLI – no web server required
* Fancy colored output in terminal

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
	 * PASS ok() the integer '1' is okay
	 * FAIL ok() the integer '0' is not okay

	ERRORS/EXCEPTIONS: 0
	TOTAL ASSERTIONS: 1 pass, 1 fail, 0 expected fail, 2 total
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

* `fu::not_strict_equal($a, $b, $msg = null)`    
  Assert that $a !== $b. Optional $msg describes the test

* `fu::has($needle, $haystack, $msg = null)`    
  Assert that an array or object (`$haystack`) has a key or property (`$needle`)

* `fu::fail($msg = null, [$expected = null])`    
  Force a failed assertion. If `$expected === true`, it's marked as an *expected failure*

* `fu::expect_fail($msg = null)`    
  Assets an *expected failure.* Equivalent to `fu::fail('msg', true)`

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


## Installation
### Install with Composer
If you're using [Composer](https://github.com/composer/composer) to manage dependencies, you can add FUnit with it.

	{
		"require": {
			"funkatron/funit": ">=1.0"
		}
	}

*Note that FUnit has not yet reached 1.0!*

### Install source from GitHub
To install the source code:

	git clone git://github.com/funkatron/FUnit.git

And include it in your scripts:

	require_once '/path/to/FUnit/FUnit.php';

### Install source from zip/tarball
Alternatively, you can fetch a [tarball](https://github.com/funkatron/FUnit/tarball/master) or [zipball](https://github.com/funkatron/FUnit/zipball/master):

    $ curl https://github.com/funkatron/FUnit/tarball/master | tar xzv
    (or)
    $ wget https://github.com/funkatron/FUnit/tarball/master -O - | tar xzv

### Using a Class Loader
If you're using a class loader (e.g., [Symfony Class Loader](https://github.com/symfony/ClassLoader)) for [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)-style class loading:

	$loader->registerNamespace('FUnit', 'path/to/vendor/FUnit');
