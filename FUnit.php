<?php

namespace FUnit;


class fu {

	const PASS = 'PASS';

	const FAIL = 'FAIL';

	/**
	 * $tests['name'] => array(
	 * 		'run'=>false,
	 * 		'pass'=>false,
	 * 		'test'=>null,
	 * 		'assertions'=>array('func_name'=>'foo', 'func_args'=array('a','b'), 'result'=>$result, 'msg'=>'blahblah'),
	 */
	static $tests = array();

	static $current_test_name = null;

	static $setup_func = null;

	static $teardown_func = null;

	static $fixtures = array();

	static $errors = array();

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
	 * @see fu::run_test()
	 */
	protected static function exception_handler($e) {
		$datetime = date("Y-m-d H:i:s (T)");
		$num = 0;
		$type = get_class($e);
		$msg = $e->getMessage();
		$file = $e->getFile();
		$line = $e->getLine();

		$edata = compact('datetime', 'num', 'type', 'msg', 'file', 'line');

		fu::add_error_data($edata);
	}


	/**
	 * custom error handler to catch errors triggered while running tests. this is
	 * registered at the start of fu::run() and deregistered at stop
	 * @see fu::run()
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

		$edata = compact('datetime', 'num', 'type', 'msg', 'file', 'line');

		fu::add_error_data($edata);
	}

	/**
	 * adds error data to the main $errors var property and the current test's
	 * error array
	 * @param array $edata ['datetime', 'num', 'type', 'msg', 'file', 'line']
	 * @see fu::$errors
	 * @see fu::error_handler()
	 * @see fu::exception_handler()
	 */
	protected static function add_error_data($edata) {

		fu::$errors[] = $edata;

		if (static::$current_test_name) {
			static::$tests[static::$current_test_name]['errors'][] = $edata;
		}
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
	 * @see fu::$TERM_COLORS
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

	/**
	 * Output a report. Currently only supports text output
	 *
	 * @param string $format default is 'text'
	 * @see fu::report_text()
	 */
	public static function report($format = 'text') {
		switch($format) {
			case 'text':
			default:
				static::report_text();
		}
	}

	/**
	 * Output a report as text
	 *
	 * Normally you would not call this method directly
	 *
	 * @see fu::report()
	 * @see fu::run()
	 */
	protected static function report_text() {


		$total_assert_counts = static::assert_counts();
		$test_counts = static::test_counts();

		fu::out("RESULTS:");
		fu::out("--------------------------------------------");

		foreach (static::$tests as $name => $tdata) {

			$assert_counts = static::assert_counts($name);
			$test_color = $tdata['pass'] ? 'GREEN' : 'RED';
			fu::out("TEST:" . static::color(" {$name} ({$assert_counts['pass']}/{$assert_counts['total']}):", $test_color));

			foreach ($tdata['assertions'] as $ass) {
				$assert_color = $ass['result'] == static::PASS ? 'GREEN' : 'RED';
				fu::out(" * "
					. static::color("{$ass['result']}"
					. " {$ass['func_name']}("
					// @TODO we should coerce these into strings and output only on fail
					// . implode(', ', $ass['func_args'])
					. ") {$ass['msg']}", $assert_color));
			}
			if (count($tdata['errors']) > 0) {
				foreach ($tdata['errors'] as $error) {
					fu::out( " * " . static::color(strtoupper($error['type']) . ": {$error['msg']} in {$error['file']}#{$error['line']}", 'RED') );
				}
			}

			fu::out("");
		}


		$err_count = count($tdata['errors']);
		$err_color = (count($tdata['errors']) > 0) ? 'RED' : 'WHITE';
		fu::out("ERRORS/EXCEPTIONS: "
			. static::color($err_count, $err_color) );


		fu::out("ASSERTIONS: "
				. static::color("{$total_assert_counts['pass']} pass", 'GREEN') . ", "
				. static::color("{$total_assert_counts['fail']} fail", 'RED') . ", "
				. static::color("{$total_assert_counts['expected_fail']} expected fail", 'YELLOW') . ", "
				. static::color("{$total_assert_counts['total']} total", 'WHITE'));

		fu::out("TESTS: {$test_counts['run']} run, "
				. static::color("{$test_counts['pass']} pass", 'GREEN') . ", "
				. static::color("{$test_counts['total']} total", 'WHITE'));
	}

	/**
	 * add a test to be executed
	 *
	 * Normally you would not call this method directly
	 * @param string $name the name of the test
	 * @param Closure $test the function to execute for the test
	 */
	protected static function add_test($name, \Closure $test) {
		static::$tests[$name] = array(
			'run' => false,
			'pass' => false,
			'test' => $test,
			'errors' => array(),
			'assertions' => array(),
		);
	}

	/**
	 * add the result of an assertion
	 *
	 * Normally you would not call this method directly
	 *
	 * @param string $func_name the name of the assertion function
	 * @param array $func_args the arguments for the assertion. Really just the $a (actual) and $b (expected)
	 * @param mixed $result this is expected to be truthy or falsy, and is converted into fu::PASS or fu::FAIL
	 * @param string $msg optional message describing the assertion
	 * @param bool $expected_fail optional expectation of the assertion to fail
	 * @see fu::ok()
	 * @see fu::equal()
	 * @see fu::not_equal()
	 * @see fu::strict_equal()
	 * @see fu::not_strict_equal()
	 */
	protected static function add_assertion_result($func_name, $func_args, $result, $msg = null, $expected_fail = false) {
		$result = ($result) ? static::PASS : static::FAIL;
		static::$tests[static::$current_test_name]['assertions'][] = compact('func_name', 'func_args', 'result', 'msg', 'expected_fail');
	}

	/**
	 * Normally you would not call this method directly
	 *
	 * Run a single test of the passed $name
	 *
	 * @param string $name the name of the test to run
	 * @see fu::run_tests()
	 * @see fu::setup()
	 * @see fu::teardown()
	 * @see fu::test()
	 */
	protected static function run_test($name) {
		fu::out("Running test '{$name}...'");

		// to associate the assertions in a test with the test,
		// we use this static var to avoid the need to for globals
		static::$current_test_name = $name;
		$test = static::$tests[$name]['test'];

		// setup
		if (isset(static::$setup_func)) {
			$setup_func = static::$setup_func;
			$setup_func();
			unset($setup_func);
		}

		try {

			$test();

		} catch(\Exception $e) {

			static::exception_handler($e);

		}

		// teardown
		if (isset(static::$teardown_func)) {
			$teardown_func = static::$teardown_func;
			$teardown_func();
			unset($teardown_func);
		}

		static::$current_test_name = null;
		static::$tests[$name]['run'] = true;

		if (count(static::$tests[$name]['errors']) > 0) {

			static::$tests[$name]['pass'] = false;

		} else {

			$assert_counts = static::assert_counts($name);
			if ($assert_counts['pass'] === $assert_counts['total']) {
				static::$tests[$name]['pass'] = true;
			} else {
				static::$tests[$name]['pass'] = false;
			}
		}

		return static::$tests[$name];

	}

	/**
	 * Normally you would not call this method directly
	 *
	 * Run all of the registered tests
	 *
	 * @see fu::run()
	 * @see fu::run_test()
	 */
	public static function run_tests() {
		foreach (static::$tests as $name => &$test) {
			static::run_test($name);
		}
	}

	/**
	 * Normally you would not call this method directly
	 *
	 * Retrieves stats about assertions run. returns an array with the keys 'total', 'pass', 'fail', 'expected_fail'
	 *
	 * If called without passing a test name, retrieves info about all assertions. Else just for the named test
	 *
	 * @param string $test_name optional the name of the test about which to get assertion stats
	 * @return array has keys 'total', 'pass', 'fail', 'expected_fail'
	 */
	protected static function assert_counts($test_name = null) {

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
				if ($ass['result'] === fu::PASS) {
					$pass++;
				} elseif ($ass['result'] === fu::FAIL) {
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
			$assertions = static::$tests[$test_name]['assertions'];
			$rs = $test_asserts($test_name, $assertions);
			$total += $rs['total'];
			$pass += $rs['pass'];
			$fail += $rs['fail'];
			$expected_fail += $rs['expected_fail'];
		} else {
			foreach (static::$tests as $test_name => $tdata) {
				$assertions = static::$tests[$test_name]['assertions'];
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
	 * Normally you would not call this method directly
	 *
	 * Retrieves stats about tests run. returns an array with the keys 'total', 'pass', 'run'
	 *
	 * @param string $test_name optional the name of the test about which to get assertion stats
	 * @return array has keys 'total', 'pass', 'run'
	 */
	protected static function test_counts() {
		$total = count(static::$tests);
		$run = 0;
		$pass = 0;

		foreach (static::$tests as $test_name => $tdata) {
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
	 * helper to deal with scoping fixtures. To store a fixture:
	 * 	fu::fixture('foo', 'bar');
	 * to retrieve a fixture:
	 * 	fu::fixture('foo');
	 *
	 * I wish we didn't have to do this. In PHP 5.4 we may just be
	 * able to bind the tests to an object and access fixtures via $this
	 *
	 * @param string $key the key to set or retrieve
	 * @param mixed $val the value to assign to the key. OPTIONAL
	 * @see fu::setup()
	 * @return mixed the value of the $key passed.
	 */
	public static function fixture($key, $val = null) {
		if (isset($val)) {
			static::$fixtures[$key] = $val;
		}

		return static::$fixtures[$key];
	}

	/**
	 * removes all fixtures. This won't magically close connections or files, tho
	 *
	 * @see fu::fixture()
	 * @see fu::teardown()
	 */
	public static function reset_fixtures() {
		static::$fixtures = array();
	}

	/**
	 * register a function to run at the start of each test
	 *
	 * typically you'd use the passed function to register some fixtures
	 *
	 * @param Closure $setup an anon function
	 * @see fu::fixture()
	 */
	public static function setup(\Closure $setup) {
		static::$setup_func = $setup;
	}

	/**
	 * register a function to run at the end of each test
	 *
	 * typically you'd use the passed function to close/clean-up any fixtures you made
	 *
	 * @param Closure $teardown an anon function
	 * @see fu::fixture()
	 * @see fu::reset_fixtures()
	 */
	public static function teardown(\Closure $teardown) {
		static::$teardown_func = $teardown;
	}

	/**
	 * add a test to be run
	 *
	 * @param string $name the name for the test
	 * @param Closure $test the test function
	 */
	public static function test($name, \Closure $test) {
		static::add_test($name, $test);
	}

	/**
	 * assert that $a is equal to $b. Uses `==` for comparison
	 *
	 * @param mixed $a the actual value
	 * @param mixed $b the expected value
	 * @param string $msg optional description of assertion
	 */
	public static function equal($a, $b, $msg = null) {
		$rs = ($a == $b);
		static::add_assertion_result(__FUNCTION__, array($a, $b), $rs, $msg);
	}

	/**
	 * assert that $a is not equal to $b. Uses `!=` for comparison
	 *
	 * @param mixed $a the actual value
	 * @param mixed $b the expected value
	 * @param string $msg optional description of assertion
	 */
	public static function not_equal($a, $b, $msg = null) {
		$rs = ($a != $b);
		static::add_assertion_result(__FUNCTION__, array($a, $b), $rs, $msg);
	}

	/**
	 * assert that $a is strictly equal to $b. Uses `===` for comparison
	 *
	 * @param mixed $a the actual value
	 * @param mixed $b the expected value
	 * @param string $msg optional description of assertion
	 */
	public static function strict_equal($a, $b, $msg = null) {
		$rs = ($a === $b);
		static::add_assertion_result(__FUNCTION__, array($a, $b), $rs, $msg);
	}

	/**
	 * assert that $a is strictly not equal to $b. Uses `!==` for comparison
	 *
	 * @param mixed $a the actual value
	 * @param mixed $b the expected value
	 * @param string $msg optional description of assertion
	 */
	public static function not_strict_equal($a, $b, $msg = null) {
		$rs = ($a !== $b);
		static::add_assertion_result(__FUNCTION__, array($a, $b), $rs, $msg);
	}

	/**
	 * assert that $a is truthy. Casts $a to boolean for result
	 * @param mixed $a the actual value
	 * @param string $msg optional description of assertion
	 */
	public static function ok($a, $msg = null) {
		$rs = (bool)$a;
		static::add_assertion_result(__FUNCTION__, array($a), $rs, $msg);
	}

	/**
	 * assert that $haystack has a key or property named $needle. If $haystack
	 * is neither, returns false
	 * @param string $needle the key or property to look for
	 * @param array|object $haystack the array or object to test
	 * @param string $msg optional description of assertion
	 */
	public static function has($needle, $haystack, $msg = null) {
		if (is_object($haystack)) {
			$rs = (bool)property_exists($haystack, $needle);
		} elseif (is_array($haystack)) {
			$rs = (bool)array_key_exists($needle, $haystack);
		} else {
			$rs = false;
		}

		static::add_assertion_result(__FUNCTION__, array($needle, $haystack), $rs, $msg);
	}
	/**
	 * Force a failed assertion
	 * @param string $msg optional description of assertion
	 * @param bool $exptected optionally expect this test to fail
	 */
	public static function fail($msg = null, $expected = false) {
		static::add_assertion_result(__FUNCTION__, array(), false, $msg, $expected);
	}

	/**
	 * Fail an assertion in an expected way
	 * @param string $msg optional description of assertion
	 * @param bool $exptected optionally expect this test to fail
	 * @see fu::fail()
	 */
	public static function expect_fail($msg = null) {
		return static::fail($msg, true);
	}

	/**
	 * Run the registered tests, and output a report
	 *
	 * @param boolean $report whether or not to output a report after tests run. Default true.
	 * @see fu::run_tests()
	 * @see fu::report()
	 */
	public static function run($report = true) {

		// set handlers
		$old_error_handler = set_error_handler('\FUnit\fu::error_handler');

		static::run_tests();
		if ($report) { static::report(); }

		// restore handlers
		if ($old_error_handler) {
			set_error_handler($old_error_handler);
		}
	}

	/**
	 * @TODO
	 */
	public function expect($int) {}
}
