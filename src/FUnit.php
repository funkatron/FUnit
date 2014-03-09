<?php

/**
 * I like autoloaders but I don't want to require them
 */
require_once __DIR__ . "/TestSuite.php";

class FUnit {

	const VERSION = '0.6.0';

	const PASS = 'PASS';

	const FAIL = 'FAIL';

	const DEFAULT_SUITE_NAME = 'default';

	/**
	 * debug mode
	 */
	public static $DEBUG = false;
	public static $DEBUG_COLOR = 'BLUE';

	public static $INFO_COLOR = 'WHITE';

	/**
	 * if `true`, nothing will be output but the report
	 * @var boolean
	 */
	public static $SILENCE = false;

	/**
	 * $suites['name'] => \FUnit\TestSuite
	 */
	public static $suites = array();

	/**
	 * $tests['name'] => array(
	 * 		'run'=>false,
	 * 		'skipped'=>false,
	 * 		'pass'=>false,
	 * 		'test'=>null,
	 * 		'assertions'=>array('func_name'=>'foo', 'func_args'=array('a','b'), 'result'=>$result, 'msg'=>'blahblah'),
	 * 		'timing' => array('setup'=>ts, 'run'=>ts, 'teardown'=>ts, 'total'=ts),
	 */
	public static $all_run_tests = array();

	static $current_suite_name = null;

	static $setup_func = null;

	static $teardown_func = null;

	static $fixtures = array();

	static $errors = array();

	/**
	 * if `true`, will not output a report
	 * @var boolean
	 */
	static $disable_reporting = false;

	/**
	 * this is used by the test runner utility to suppress FUnit::run() calls
	 * in `require`d files
	 * @var boolean
	 */
	static $disable_run = false;

	protected static $TERM_COLORS = array(
		'BLACK' => "30",
		'RED' => "31",
		'GREEN' => "32",
		'YELLOW' => "33",
		'BLUE' => "34",
		'MAGENTA' => "35",
		'CYAN' => "36",
		'WHITE' => "37",
		'DEFAULT' => "00",
	);

	/**
	 * custom exception handler, massaging the format into the same we use for Errors
	 *
	 * We don't actually use this as a proper exception handler, so we can continue execution.
	 *
	 * @param Exception $e
	 * @return array ['datetime', 'num', 'type', 'msg', 'file', 'line']
	 * @see FUnit::run_test()
	 */
	public static function exception_handler($e) {
		$datetime = date("Y-m-d H:i:s (T)");
		$num = 0;
		$type = get_class($e);
		$msg = $e->getMessage();
		$file = $e->getFile();
		$line = $e->getLine();
		$bt_raw = $e->getTrace();

		array_unshift($bt_raw, array(
			'file' => $file,
			'line' => $line,
			'msg' => $msg,
			'num' => $num,
			'type' => $type,
			'datetime' => $datetime,
		));

		$backtrace = static::parse_backtrace($bt_raw);

		$edata = compact('datetime', 'num', 'type', 'msg', 'file', 'line', 'backtrace');

		FUnit::add_error_data($edata);
	}


	/**
	 * custom error handler to catch errors triggered while running tests. this is
	 * registered at the start of FUnit::run() and deregistered at stop
	 * @see FUnit::run()
	 * @internal
	 */
	public static function error_handler($num, $msg, $file, $line, $vars) {

		$datetime = date("Y-m-d H:i:s (T)");

		$types = array (
					E_ERROR              => 'Error',
					E_WARNING            => 'Warning',
					E_PARSE              => 'Parsing Error',
					E_NOTICE             => 'Notice',
					E_CORE_ERROR         => 'Core Error',
					E_CORE_WARNING       => 'Core Warning',
					E_COMPILE_ERROR      => 'Compile Error',
					E_COMPILE_WARNING    => 'Compile Warning',
					E_USER_ERROR         => 'User Error',
					E_USER_WARNING       => 'User Warning',
					E_USER_NOTICE        => 'User Notice',
					E_STRICT             => 'Runtime Notice',
					E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
					);

		$type = $types[$num];

		$bt_raw = debug_backtrace();
		array_shift($bt_raw);
		$backtrace = static::parse_backtrace($bt_raw);

		$edata = compact('datetime', 'num', 'type', 'msg', 'file', 'line', 'backtrace');

		FUnit::add_error_data($edata);
	}


	/**
	 * this generates an array of formatted strings to represent the backtrace
	 * @param  array $bt_raw the raw backtrace array
	 * @return array      an array of strings
	 * @see FUnit::error_handler()
	 * @see FUnit::exception_handler()
	 */
	protected static function parse_backtrace($bt_raw) {
		$backtrace = array();
		foreach ($bt_raw as $bt) {
			if (isset($bt['function']) && __FUNCTION__ == $bt['function'] && isset($bt['class']) && __CLASS__ == $bt['class']) {
				continue; // don't bother backtracing
			}
			if (isset($bt['file'], $bt['line'])) {
				$trace = $bt['file'] . '#' . $bt['line'];
			} else {
				$trace = '';
			}
			if (isset($bt['class']) && isset($bt['function'])) {
				$trace .= " {$bt['class']}::{$bt['function']}(...)";
			} elseif (isset($bt['function'])) {
				$trace .= " {$bt['function']}(...)";
			}
			$backtrace[] = $trace;

		}
		return $backtrace;
	}

	/**
	 * adds error data to the main $errors var property and the current test's
	 * error array
	 * @param array $edata ['datetime', 'num', 'type', 'msg', 'file', 'line']
	 * @see FUnit::$errors
	 * @see FUnit::error_handler()
	 * @see FUnit::exception_handler()
	 */
	protected static function add_error_data($edata) {
		static::check_current_suite();
		static::get_current_suite()->addErrorData($edata);
	}


	/**
	 * Format a line for printing. Detects
	 * if the script is being run from the command
	 * line or from a browser; also detects TTY for color (so pipes work).
	 *
	 * Colouring code loosely based on
	 * http://www.zend.com//code/codex.php?ozid=1112&single=1
	 *
	 * @param string $line
	 * @param string $color default is 'DEFAULT'
	 * @see FUnit::$TERM_COLORS
	 */
	protected static function color($txt, $color='DEFAULT') {
		if (PHP_SAPI === 'cli') {
			// only color if output is a posix TTY
			if (function_exists('posix_isatty') && posix_isatty(STDOUT)) {
				$color = static::$TERM_COLORS[$color];
				$txt = chr(27) . "[0;{$color}m{$txt}" . chr(27) . "[00m";
			}
			// otherwise, don't touch $txt
		} else {
			$color = strtolower($color);
			$txt = "<span style=\"color: $color;\">$txt</span>";
		}
		return $txt;
	}

	protected static function out($str) {
		if (PHP_SAPI === 'cli') {
			echo $str . "\n";
		} else {
			echo "<div>"  . nl2br($str) . "</div>";
		}
	}

	public static function debug_out($str) {
		if (!static::$DEBUG || static::$SILENCE) {
			return;
		}
		static::out(static::color($str, static::$DEBUG_COLOR));
	}

	public static function info_out($str) {
		if (static::$SILENCE) {
			return;
		}
		static::out(static::color($str, static::$INFO_COLOR));
	}

	/**
	 * @internal
	 */
	public static function report_out($str) {
		static::out($str);
	}

	/**
	 * Output a report
	 *
	 * @param string $format  'text' (default) or 'xunit'.
	 * @param array $tests_data  set of test data
	 * @see FUnit::report_text()
	 * @see FUnit::report_xunit()
	 */
	public static function report($format = 'text', array $tests_data = array()) {

		switch($format) {
			case 'xunit':
				static::report_xunit($tests_data);
				break;
			case 'text':
				static::report_text($tests_data);
				break;
			default:
				static::report_text($tests_data);
		}
	}

	/**
	 * output a report for all tests than have been run from all suites
	 * @param string $format  'text' (default) or 'xunit'.
	 * @see FUnit::report_text()
	 * @see FUnit::report_xunit()
	 */
	public static function report_all_tests($format) {
		return static::report($format, static::$all_run_tests);
	}


	/**
	 * Output a report as text
	 *
	 * Normally you would not call this method directly
	 *
	 * @see FUnit::report()
	 * @see FUnit::run()
	 */
	protected static function report_text(array $tests) {

		$total_assert_counts = static::assertion_stats($tests);
		$test_counts = static::test_stats($tests);

		static::report_out("RESULTS:");
		static::report_out("--------------------------------------------");

		foreach ($tests as $name => $tdata) {

			$assert_counts = static::assertion_stats($tests, $name);
			if ($tdata['pass']) {
				$test_color = 'GREEN';
			} else {
				if (($assert_counts['total'] - $assert_counts['expected_fail']) == $assert_counts['pass']) {
					$test_color = 'YELLOW';
				} else {
					$test_color = 'RED';
				}
			}
			static::report_out("TEST:" . static::color(" {$name} ({$assert_counts['pass']}/{$assert_counts['total']}):", $test_color));

			foreach ($tdata['assertions'] as $ass) {
				if ($ass['expected_fail']) {
					$assert_color = 'YELLOW';
				} else {
					$assert_color = $ass['result'] == static::PASS ? 'GREEN' : 'RED';
				}
				static::report_out(" * "
					. static::color("{$ass['result']}"
					. " {$ass['func_name']}("
					// @TODO we should coerce these into strings and output only on fail
					// . implode(', ', $ass['func_args'])
					. ") {$ass['msg']}" . ($ass['expected_fail']? ' (expected)' : ''), $assert_color));
			}
			if (count($tdata['errors']) > 0) {
				$bt = '';
				foreach ($tdata['errors'] as $error) {
					$sep = "\n  -> ";
					$bt = $sep . implode($sep, $error['backtrace']);
					static::report_out(
						' * ' . static::color(
							strtoupper($error['type']) . ": {$error['msg']} in {$bt}",
							'RED')
					);
				}
			}

			static::report_out("");
		}


		$err_count = count($tdata['errors']);
		$err_color = (count($tdata['errors']) > 0) ? 'RED' : 'WHITE';
		static::report_out("ERRORS/EXCEPTIONS: "
			. static::color($err_count, $err_color) );


		static::report_out("ASSERTIONS: "
				. static::color("{$total_assert_counts['pass']} pass", 'GREEN') . ", "
				. static::color("{$total_assert_counts['fail']} fail", 'RED') . ", "
				. static::color("{$total_assert_counts['expected_fail']} expected fail", 'YELLOW') . ", "
				. static::color("{$total_assert_counts['total']} total", 'WHITE'));

		static::report_out("TESTS: {$test_counts['run']} run, "
				. static::color("{$test_counts['pass']} pass", 'GREEN') . ", "
				. static::color("{$test_counts['total']} total", 'WHITE'));
	}

	/**
	 * Output a report as xunit format (Jenkins-compatible)
	 *
	 * This should definitely use one of the xml-specific build/output methods, but really... life is too short
	 *
	 * @see FUnit::report()
	 * @see FUnit::run()
	 */
	protected static function report_xunit(array $tests) {

		$counts = static::test_stats($tests);
		$xml = "<?xml version=\"1.0\"?>\n";
		$xml .= "<testsuite tests=\"{$counts['total']}\">\n";
		foreach ($tests as $name => $tdata) {
			$xml .= "    <testcase classname=\"funit.{$name}\" name=\"{$name}\" time=\"0\">\n";
			if (!$tdata['pass']) {
				$xml .= "<failure/>";
			}
			$xml .= "</testcase>\n";
		}
		$xml .= "</testsuite>\n";
		FUnit::report_out($xml);
	}

	/**
	 * Retrieves stats about tests run. returns an array with the keys
	 * 'total', 'pass', 'run'
	 *
	 * @param  array $tests  a set of test results
	 * @return array has keys 'total', 'pass', 'run'
	 */
	public static function test_stats(array $tests) {
		$total = count($tests);
		$run = 0;
		$pass = 0;

		foreach ($tests as $test_name => $tdata) {
			if ($tdata['pass']) {
				$pass++;
			}
			if ($tdata['run']) {
				$run++;
			}
		}

		return compact('total', 'pass', 'run');
	}

	/**
	 * Retrieves stats about assertions run. returns an array with the keys 'total', 'pass', 'fail', 'expected_fail'
	 *
	 * If called without passing a test name, retrieves info about all assertions. Else just for the named test
	 *
	 * @param  array  $tests     a set of test results
	 * @param string $test_name optional the name of the test about which to get assertion stats
	 * @return array  has keys 'total', 'pass', 'fail', 'expected_fail'
	 */
	public static function assertion_stats(array $tests, $test_name = null) {
		$total = 0;
		$pass  = 0;
		$fail  = 0;
		$expected_fail = 0;

		$test_asserts = function($test_name, $assertions) {

			$total = 0;
			$pass  = 0;
			$fail  = 0;
			$expected_fail = 0;

			foreach ($assertions as $ass) {
				if ($ass['result'] === \FUnit::PASS) {
					$pass++;
				} elseif ($ass['result'] === \FUnit::FAIL) {
					$fail++;
					if ($ass['expected_fail']) {
						$expected_fail++;
					}
				}
				$total++;
			}

			return compact('total', 'pass', 'fail', 'expected_fail');

		};

		if ($test_name) {
			$assertions = $tests[$test_name]['assertions'];
			$rs = $test_asserts($test_name, $assertions);
			$total += $rs['total'];
			$pass += $rs['pass'];
			$fail += $rs['fail'];
			$expected_fail += $rs['expected_fail'];
		} else {
			foreach ($tests as $test_name => $tdata) {
				$assertions = $tests[$test_name]['assertions'];
				$rs = $test_asserts($test_name, $assertions);
				$total += $rs['total'];
				$pass += $rs['pass'];
				$fail += $rs['fail'];
				$expected_fail += $rs['expected_fail'];
			}
		}

		return compact('total', 'pass', 'fail', 'expected_fail');
	}


	/**
	 * add a test to be executed
	 *
	 * Normally you would not call this method directly
	 * @param string $name the name of the test
	 * @param Closure $test the function to execute for the test
	 */
	protected static function add_test($name, \Closure $test) {
		$suite = static::get_current_suite();
		$suite->addTest($name, $test);
	}

	/**
	 * add a test suite
	 * @param string $name the name associated with the suite
	 */
	protected static function add_suite($name = self::DEFAULT_SUITE_NAME) {
		$inc = 0;
		$orig_name = $name;
		while (array_key_exists($name, static::$suites)) {
			$inc++;
			$name = "{$orig_name}_" . ($inc);
		}

		$suite = new FUnit\TestSuite($name);
		static::$suites[$name] = $suite;
		return $suite;
	}

	/**
	 * check if a current suite exists. If not, create a new one and assign
	 * its name to static::$current_suite_name
	 */
	protected static function check_current_suite() {
		if (!static::$current_suite_name) {
			$suite = static::add_suite();
			static::$current_suite_name = $suite->getName();
		}
	}

	/**
	 * get an FUnit\TestSuite by name
	 * @param  string $name
	 * @return FUnit\TestSuite
	 */
	public static function get_suite($name)
	{
		if (!array_key_exists($name, static::$suites)) {
			return null;
		}
		return static::$suites[$name];

	}

	/**
	 * get the current suite. If none current, return null
	 * @return FUnit\TestSuite|null
	 */
	public static function get_current_suite() {
		if (!static::$current_suite_name) {
			return null;
		}
		return static::$suites[static::$current_suite_name];
	}


	/**
	 * add the result of an assertion
	 *
	 * Normally you would not call this method directly
	 *
	 * @param string $func_name the name of the assertion function
	 * @param array $func_args the arguments for the assertion. Really just the $a (actual) and $b (expected)
	 * @param mixed $result this is expected to be truthy or falsy, and is converted into FUnit::PASS or FUnit::FAIL
	 * @param string $msg optional message describing the assertion
	 * @param bool $expected_fail optional expectation of the assertion to fail
	 * @see FUnit::ok()
	 * @see FUnit::equal()
	 * @see FUnit::not_equal()
	 * @see FUnit::strict_equal()
	 * @see FUnit::not_strict_equal()
	 */
	protected static function add_assertion_result($func_name, $func_args, $result, $msg = null, $expected_fail = false) {
		$suite = static::get_current_suite();
		$suite->addAssertionResult($func_name, $func_args, $result, $msg, $expected_fail);
	}

	/**
	 * Normally you would not call this method directly
	 *
	 * Run a single test of the passed $name
	 *
	 * @param string $name the name of the test to run
	 * @see FUnit::run_tests()
	 * @see FUnit::setup()
	 * @see FUnit::teardown()
	 * @see FUnit::test()
	 */
	protected static function run_test($name) {
		$suite = static::get_current_suite();
		return $suite->run_test($name);
	}

	/**
	 * Normally you would not call this method directly
	 *
	 * Run all of the registered tests
	 * @param string $filter optional test case name filter
	 * @see FUnit::run()
	 * @see FUnit::run_test()
	 * @internal
	 */
	public static function run_tests($filter = null) {
		static::check_current_suite();
		$suite = static::get_current_suite();
		$suite->run_tests($filter);
	}

	/**
	 * helper to deal with scoping fixtures. To store a fixture:
	 * 	FUnit::fixture('foo', 'bar');
	 * to retrieve a fixture:
	 * 	FUnit::fixture('foo');
	 *
	 * I wish we didn't have to do this. In PHP 5.4 we may just be
	 * able to bind the tests to an object and access fixtures via $this
	 *
	 * @param string $key the key to set or retrieve
	 * @param mixed $val the value to assign to the key. OPTIONAL
	 * @see FUnit::setup()
	 * @return mixed the value of the $key passed.
	 */
	public static function fixture($key, $val = null) {
		static::check_current_suite();
		static::get_current_suite()->fixture($key, $val);
	}

	/**
	 * removes all fixtures. This won't magically close connections or files, tho
	 *
	 * @see FUnit::fixture()
	 * @see FUnit::teardown()
	 */
	public static function reset_fixtures() {
		static::check_current_suite();
		static::get_current_suite()->resetFixtures();
	}

	/**
	 * register a function to run at the start of each test
	 *
	 * typically you'd use the passed function to register some fixtures
	 *
	 * @param Closure $setup an anon function
	 * @see FUnit::fixture()
	 */
	public static function setup(\Closure $setup) {
		static::check_current_suite();
		static::get_current_suite()->setup($setup);
	}

	/**
	 * register a function to run at the end of each test
	 *
	 * typically you'd use the passed function to close/clean-up any fixtures you made
	 *
	 * @param Closure $teardown an anon function
	 * @see FUnit::fixture()
	 * @see FUnit::reset_fixtures()
	 */
	public static function teardown(\Closure $teardown) {
		static::check_current_suite();
		static::get_current_suite()->teardown($teardown);
	}

	/**
	 * add a test to be run
	 *
	 * @param string $name the name for the test
	 * @param Closure $test the test function
	 */
	public static function test($name, \Closure $test) {
		static::check_current_suite();
		static::get_current_suite()->addTest($name, $test);
	}

	/**
	 * initialize a new test suite. adds a suite to \FUnit::$suites and sets
	 * \FUnit::$current_suite_name to the new suite's name
	 * @param  string $name default is FUnit::DEFAULT_SUITE_NAME
	 */
	public static function suite($name = self::DEFAULT_SUITE_NAME) {
		$suite = static::add_suite($name);
		static::$current_suite_name = $suite->getName();
	}

	/**
	 * We use this magic method to map various assertion calls to assert_{$name}()
	 * This is so we can break out the call to add_assertion_result() and test
	 * the assertion methods properly
	 * @param  string $name      the assertion short name
	 * @param  array $arguments  arguments to pass to "\FUnit::{$assert_name}()"
	 * @return [type]            [description]
	 */
	public static function __callStatic($name, $arguments)
	{
		$assert_name = 'assert_' . $name;
		if (method_exists('\FUnit', $assert_name)) {

			switch ($assert_name) {
				case "assert_fail":
					$expected_fail = array_pop($arguments);
					$msg = array_pop($arguments);
					break;
				default:
					$expected_fail = false;
					$msg = array_pop($arguments);
			}
			$rs = call_user_func_array("\FUnit::{$assert_name}", $arguments);
			static::add_assertion_result($name, $arguments, $rs, $msg, $expected_fail);
			return $rs;
		}
	}


	/**
	 * assert that $a is equal to $b. Uses `==` for comparison
	 *
	 * @param mixed $a the actual value
	 * @param mixed $b the expected value
	 * @param string $msg optional description of assertion
	 */
	public static function assert_equal($a, $b, $msg = null) {
		$rs = ($a == $b);
		if (!$rs) {
			static::debug_out('Expected: ' . var_export($a, true) . ' and ' . var_export($b, true) . ' to be loosely equal');
		}
		return $rs;
	}

	/**
	 * assert that $a is not equal to $b. Uses `!=` for comparison
	 *
	 * @param mixed $a the actual value
	 * @param mixed $b the expected value
	 * @param string $msg optional description of assertion
	 */
	public static function assert_not_equal($a, $b, $msg = null) {
		$rs = ($a != $b);
		if (!$rs) {
			static::debug_out('Expected: ' . var_export($a, true) . ' and ' . var_export($b, true) . ' to be unequal');
		}
		return $rs;
	}

	/**
	 * assert that $a is strictly equal to $b. Uses `===` for comparison
	 *
	 * @param mixed $a the actual value
	 * @param mixed $b the expected value
	 * @param string $msg optional description of assertion
	 */
	public static function assert_strict_equal($a, $b, $msg = null) {
		$rs = ($a === $b);
		if (!$rs) {
			static::debug_out('Expected: ' . var_export($a, true) . ' and ' . var_export($b, true) . ' to be strictly equal');
		}
		return $rs;
	}

	/**
	 * assert that $a is strictly not equal to $b. Uses `!==` for comparison
	 *
	 * @param mixed $a the actual value
	 * @param mixed $b the expected value
	 * @param string $msg optional description of assertion
	 */
	public static function assert_not_strict_equal($a, $b, $msg = null) {
		$rs = ($a !== $b);
		if (!$rs) {
			static::debug_out('Expected: ' . var_export($a, true) . ' and ' .
							  var_export($b, true) . ' to be strictly unequal');
		}
		return $rs;
	}

	/**
	 * assert that $a is truthy. Casts $a to boolean for result
	 * @param mixed $a the actual value
	 * @param string $msg optional description of assertion
	 */
	public static function assert_ok($a, $msg = null) {
		$rs = (bool)$a;
		if (!$rs) {
			static::debug_out('Expected: ' . var_export($a, true) . ' to be truthy');
		}
		return $rs;
	}

	/**
	 * assert that $a is falsy. Casts $a to boolean for result
	 * @param mixed $a the actual value
	 * @param string $msg optional description of assertion
	 */
	public static function assert_not_ok($a, $msg = null) {
		$rs = !(bool)$a;
		if (!$rs) {
			static::debug_out('Expected: ' . var_export($a, true) . ' to be falsy');
		}
		return $rs;
	}


	/**
	 * Iterate over all the items in `$a` and pass each to `$callback`. If the
	 * callback returns `true` for all, it passes -- otherwise it fails
	 * @param  array|Traversable   $a        an array or Traversable (iterable) object
	 * @param  callable $callback [description]
	 * @param string $msg optional description of assertion
	 */
	public static function assert_all_ok($a, callable $callback, $msg = null) {
		if (is_array($a) || $a instanceof \Traversable) {
			$rs = true;
			$failed_val = null;
			foreach ($a as $value) {
				if (!call_user_func($callback, $value)) {
					$rs = false;
					$failed_val = $value;
					break;
				}
			}
		} else {
			static::debug_out("\$a was not an array or Traversable");

			$failed_val = null;
			$rs = false;
		}

		if (!$rs) {
			static::debug_out('Expected: ' . var_export($a, true) .
							  ' to return true in callback, but ' .
							  var_export($failed_val, true) .
							  ' returned false');
		}
		return $rs;
	}


	/**
	 * assert that $callback throws an exception of type $exception
	 *
	 * If $params is an array, it is passed as arguments to the callback.
	 * Otherwise, it is assumed no arguments need to be passed.
	 *
	 * @param callable $callback Callback that should throw an exception
	 * @param array $params Callback that should throw an exception
	 * @param string $exception The exception class that should be thrown
	 * @param string $msg
	 * @return bool
	 */
	public static function assert_throws(callable $callback, $params, $exception = null, $msg = null) {
		if (is_array($params)) {
			$exception = $exception ?: 'Exception';
		} else {
			$msg = $exception;
			$exception = $params;
			$params = array();
		}
		try {
			call_user_func_array($callback, $params);
			$rs = false;
		} catch (\Exception $e) {
			$rs = $e instanceof $exception;
		}
		if (!$rs) {
			$txt = isset($e) ? 'got ' . get_class($e) : 'no exception thrown';
			static::debug_out('Expected exception ' . $exception . ', but ' . $txt);
		}
		return $rs;
	}

	/**
	 * assert that $haystack has a key or property named $needle. If $haystack
	 * is neither, returns false
	 * @param string $needle the key or property to look for
	 * @param array|object $haystack the array or object to test
	 * @param string $msg optional description of assertion
	 */
	public static function assert_has($needle, $haystack, $msg = null) {
		if (is_object($haystack)) {
			$rs = (bool)property_exists($haystack, $needle);
		} elseif (is_array($haystack)) {
			$rs = (bool)array_key_exists($needle, $haystack);
		} else {
			$rs = false;
		}

		if (!$rs) {
			static::debug_out('Expected: ' . var_export($haystack, true) . ' to contain ' . var_export($needle, true));
		}
		return $rs;
	}

	/**
	 * assert that $haystack does not have a key or property named $needle. If $haystack
	 * is neither an array or object, returns false
	 * @param string $needle the key or property to look for
	 * @param array|object $haystack the array or object to test
	 * @param string $msg optional description of assertion
	 */
	public static function assert_not_has($needle, $haystack, $msg = null) {
		if (is_object($haystack)) {
			$rs = !(bool)property_exists($haystack, $needle);
		} elseif (is_array($haystack)) {
			$rs = !(bool)array_key_exists($needle, $haystack);
		} else {
			$rs = false;
		}

		if (!$rs) {
			static::debug_out('Expected: ' . var_export($haystack, true) . ' to NOT contain ' . var_export($needle, true));
		}
		return $rs;
	}

	/**
	 * Force a failed assertion
	 * @param string $msg optional description of assertion
	 * @param bool $exptected optionally expect this test to fail
	 */
	public static function assert_fail($msg = null, $expected = false) {
		return false;
	}

	/**
	 * Fail an assertion in an expected way
	 * @param string $msg optional description of assertion
	 * @param bool $exptected optionally expect this test to fail
	 * @see FUnit::fail()
	 */
	public static function assert_expect_fail($msg = null) {
		return static::assert_fail($msg, true);
	}

	/**
	 * Force a successful assertion
	 * @param string $msg optional description of assertion
	 */
	public static function assert_pass($msg = null) {
		return true;
	}

	/**
	 * Run the registered tests, and output a report
	 *
	 * @param boolean $report whether or not to output a report after tests run. Default true.
	 * @param string $filter optional test case name filter
	 * @see FUnit::run_tests()
	 * @see FUnit::report()
	 */
	public static function run($report = true, $filter = null, $report_format = null) {

		// create a new current suite if needed
		static::check_current_suite();

		// get the suite
		$suite = static::get_current_suite();

		if (static::$disable_run) {
			FUnit::debug_out("Not running tests because of \$disable_run");
			return;
		}

		if (static::$disable_reporting) {
			FUnit::debug_out("Reporting disabled");
			$report = false;
		}

		// set handlers
		$old_error_handler = set_error_handler('\FUnit::error_handler');

		// run the tests in the suite
		$run_tests = $suite->runTests($filter);
		if ($report) {
			static::report($report_format, $run_tests);
		}

		// add this suite's data to the static $all_run_tests
		static::$all_run_tests = array_merge(static::$all_run_tests, $run_tests);

		// restore handlers
		if ($old_error_handler) {
			set_error_handler($old_error_handler);
		}

		$exit_code = $suite->getExitCode();

		static::$current_suite_name = null;

		return $exit_code;


	}

	/**
	 * Passing `true` will disable the reporting output
	 * @param boolean $state
	 */
	public static function set_disable_reporting($state) {
		static::$disable_reporting = (bool)$state;
	}

	/**
	 * Passing `true` will disable the FUnit::run() method. This is used by
	 * the test runner utility to avoid calls to run tests within scripts
	 * @param boolean $state
	 * @private
	 */
	public static function set_disable_run($state) {
		static::$disable_run = (bool)$state;
	}

	/**
	 * if true, debugging info will be output.
	 * Note that $SILENCE will override the $DEBUG state
	 * @param boolean $state
	 */
	public static function set_debug($state) {
		static::$DEBUG = (bool)$state;
	}

	/**
	 * if $SILENCE is true, only the report will be output -- no progress etc.
	 * This will override the $DEBUG state
	 * @param boolean $state
	 */
	public static function set_silence($state) {
		static::$SILENCE = (bool)$state;
	}

	/**
	 * Retrieve the exit code. It scans all suites for their exit codes, and if
	 * AND of them are 1, returns 1. Else 0.
	 *
	 * If any test fails, the exit code will be set to `1`. Otherwise `0`
	 * @return integer 0 or 1
	 */
	public static function exit_code() {
		foreach (static::$suites as $name => $suite) {
			if ($suite->getExitCode() === 1) {
				return 1;
			}
		}
		return 0;
	}

	/**
	 * @TODO
	 */
	public function expect($int) {}
}
