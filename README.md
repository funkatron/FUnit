# FUnit: The testing microframework for PHP

FUnit is a testing microframework for PHP 5.3+, partially inspired by [QUnit](http://qunitjs.com/). FUnit was created by [Ed Finkler](http://funkatron.com) for **[Fictive Kin](http://fictivekin.com)**.

**If you can code PHP, you can write tests with FUnit.**

## Features

* Simple to write tests and get output – **start writing tests fast**
* Short, straightforward syntax
* Can be run from the CLI – no web server required
* Fancy colored output in terminal

## Example

``` php
<?php
require __DIR__ . '/FUnit.php';
use \FUnit as fu;  // note the alias to "fu" for terseness

fu::test("this is a test", function() {
	fu::ok(1, "the integer '1' is okay");
	fu::ok(0, "the integer '0' is not okay"); // this will fail!
});

$exit_code = fu::run();
exit($exit_code);
```

Will output:

	> php example_standalone.php
    Running test 'this is a test...'
	RESULTS:
	--------------------------------------------
	TEST: this is a test (1/2):
	 * PASS ok() the integer '1' is okay
	 * FAIL ok() the integer '0' is not okay

	ERRORS/EXCEPTIONS: 0
	TOTAL ASSERTIONS: 1 pass, 1 fail, 0 expected fail, 2 total
	TESTS: 1 run, 0 pass, 1 total

See the `example_standalone.php` file for more, or try running it with `php example_standalone.php`

Alternately, if you load `standalone_example.php` in a web browser, it will output a very simple HTML version of the text report. If you're running PHP 5.4 or above, you can use the dev server to view it like so:

`php -S 0.0.0.0:8000 example_standalone.php`

And then open <http://0.0.0.0:8000> in a web browser.

## Methods

### Test Suite Building Methods

* **`FUnit::test($name, \Closure $test)`**    
  Add a test with the name $name and an anonymous function $test. $test would contain various **assertions**, like `FUnit::ok()`

* **`FUnit::run($report = true, $filter = null, $report_format = 'text')`**    
  Runs the registered tests.
  * `$report` (boolean): If `false` is passed, the report output is suppressed.
  * `$filter` (string): If this is passed, only tests that contain the `$filter` string will be run.
  * `$report_format` (string): Default is 'text'. Also accepts 'xunit'.

* **`FUnit::setup(\Closure $setup)`**    
  Register a function to run at the start of each test. See `FUnit::fixture()`

* **`FUnit::teardown(\Closure $setup)`**    
  Register a function to run at the end of each test. See `FUnit::fixture()` and `FUnit::reset_fixtures()`

* **`FUnit::fixture($key, [$val])`**    
  Retrieve or register a fixture. Use this in FUnit::setup() to assign fixtures to keys, and retrieve those fixtures in your tests

* **`FUnit::reset_fixtures()`**    
  Clears out all fixtures in the FUnit::$fixtures array. This doesn't guarantee clean shutdown/close


### Assertions

* **`FUnit::ok($a, $msg = null)`**    
  Assert that $a is truthy. Optional $msg describes the test

* **`FUnit::not_ok($a, $msg = null)`**    
  Assert that $a is not truthy. Optional $msg describes the test

* **`FUnit::equal($a, $b, $msg = null)`**    
  Assert that $a == $b. Optional $msg describes the test

* **`FUnit::not_equal($a, $b, $msg = null)`**    
  Assert that $a != $b. Optional $msg describes the test

* **`FUnit::strict_equal($a, $b, $msg = null)`**    
  Assert that $a === $b. Optional $msg describes the test

* **`FUnit::not_strict_equal($a, $b, $msg = null)`**    
  Assert that $a !== $b. Optional $msg describes the test

* **`FUnit::has($needle, $haystack, $msg = null)`**    
  Assert that an array or object (`$haystack`) has a key or property (`$needle`)

* **`FUnit::not_has($needle, $haystack, $msg = null)`**    
  Assert that an array or object (`$haystack`) does *not* have a key or property (`$needle`). Forgive my grammar.

* **`FUnit::fail($msg = null, [$expected = null])`**    
  Force a failed assertion. If `$expected === true`, it's marked as an *expected failure*

* **`FUnit::expect_fail($msg = null)`**    
  Assets an *expected failure.* Equivalent to `FUnit::fail('msg', true)`

* **`FUnit::pass($msg = null)`**    
  Force a successful assertion.

* **`FUnit::throws($callback, $params, $exception = null, $msg = null)`**    
  Assert that `$callback` throws an exception of type `$exception`. `$callback` must be a [*callable*](http://php.net/manual/en/language.types.callable.php)

* **`FUnit::all_ok($a, $callback, $msg = null)`**    
  Iterate over all the items in `$a` and pass each to `$callback`. If the callback returns `true` for all, it passes -- otherwise it fails. `$callback` must be a [*callable*](http://php.net/manual/en/language.types.callable.php)

### Utility Methods

* **`FUnit::report($format = 'text')`**    
  Output the test report. If you've suppressed reporting output previously, you can use this to output the report manually.

* **`FUnit::exit_code()`**    
  Retrieve the exit code. If any test fails, the exit code will be set to `1`. Otherwise `0`. You can use this value to return a success or failure result with the PHP function `exit()`.


### Configuration Methods

* **`FUnit::set_disable_reporting($state)`**    
  If passed `true`, report will not be output after test runs finish. Re-enable by passing `false`.

* **`FUnit::set_debug($state)`**    
  If passed `true`, extra debugging info (including timing and details about assertion failures) will be output. Disable by passing `false`.

* **`FUnit::set_silence($state)`**    
  If passed `true`, only the report will be output -- no progress, debugging info, etc. Disable by passing `false`.


## Report formats

By default, FUnit outputs a colorful `text` output, formatted for the terminal. You can also output reports in `xunit`-style xml.

The report format is the third parameter of `FUnit::run()`:

Example:
``` php
// Outputs a colored text report. This is the default format.
FUnit::run(true, null, 'text');

// Outputs xUnit-style xml
FUnit::run(true, null, 'xunit');
```

### Browser output

The standard `text` report format will output as very simple HTML if the test file is loaded up through a web server. You can test this with the dev server if you're running PHP 5.4+:

`php -S 0.0.0.0:8000 test_file.php`

And then open <http://0.0.0.0:8000> in a web browser.

## CLI Test Runner Utility

FUnit was designed to not require a separate test runner tool, but it does come with one at `bin/fu` (or `vendor/bin/fu` if you've installed via Composer). `fu` allows you to run tests in a single file, a group of files in a directory, and filter what tests are run.

Examples:
* **`fu --help`**    
  Get detailed help and information on all options

* **`fu ./tests`**    
  Scan the directory `./tests` for files that have "test(s)" in their names, and run those tests

* **`fu tests.php`**    
  Execute tests in `tests.php`

* **`fu -d tests.php`**    
  Execute tests in `tests.php` with additional debugging info

* **`fu --filter="API" tests.php`**    
  Execute only the tests in tests.php that have "API" in the name

* **`fu -s tests.php`**    
  Execute the tests tests.php, but suppress all output other than the report

*Note:* When `fu` loads multiple test files, it `require`s each one. That means all the code within each is executed. Calls to `FUnit::run()` are suppressed, but non-FUnit code (like `exit()` calls or `require` statements) could cause issues.

## Installation
### Install with Composer
If you're using [Composer](https://github.com/composer/composer) to manage dependencies, you can add FUnit with it.

``` json
{
	"require": {
		"funkatron/funit": "dev-master"
	}
}
```

*Note that FUnit has not yet reached 1.0! That means BC may break!*

If you install via Composer, you can use the auto-generated autoloader to load FUnit, like so:

``` php
<?php
require "vendor/autoload.php"
use \FUnit as fu;

fu::test("this is a test", function() {
    fu::ok(1, "the integer '1' is okay");
    fu::ok(0, "the integer '0' is not okay"); // this will fail!
});

fu::run();
```

### Install source from GitHub
To install the source code:

	git clone git://github.com/funkatron/FUnit.git

And include it in your scripts:

``` php
require_once '/path/to/FUnit/FUnit.php';
```

### Install source from zip/tarball
Alternatively, you can fetch a [tarball](https://github.com/funkatron/FUnit/tarball/master) or [zipball](https://github.com/funkatron/FUnit/zipball/master):

    $ curl https://github.com/funkatron/FUnit/tarball/master | tar xzv
    (or)
    $ wget https://github.com/funkatron/FUnit/tarball/master -O - | tar xzv

### Using a Class Loader
If you're using a class loader (e.g., [Symfony Class Loader](https://github.com/symfony/ClassLoader)) for [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)-style class loading:

``` php
$loader->registerNamespace('FUnit', 'path/to/vendor/FUnit');
```

## Upgrading

If you're using a version older than 0.5, the namespace/class name changed to follow PSR-0 autoloader standards. The base class is now `\FUnit`, not `\FUnit\fu`. You can still call all your methods with `fu::XXX()` by aliasing the namespace like so:
``` php
use \FUnit as fu
```

