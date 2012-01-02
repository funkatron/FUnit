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
	 * 		'expected'=>0,
	 * 		'assertions'=>array('func_name'=>'foo', 'func_args'=array('a','b'), 'result'=>$result, 'msg'=>'blahblah'),
	 */
	static $tests = array();

	static $current_test_name = null;

	static $setup_func = null;

	static $teardown_func = null;

	public static function report($format = 'text') {
		switch($format) {
			case 'text':
			default:
				static::report_text();
		}
	}

	public static function report_text() {
		$total_assert_counts = static::assert_counts();
		$test_counts = static::test_counts();

		echo "RESULTS:\n";
		echo "--------------------------------------------\n";
		foreach (static::$tests as $name => $tdata) {
			$assert_counts = static::assert_counts($name);
			echo "TEST: {$name} ({$assert_counts['pass']}/{$assert_counts['total']}):\n";
			foreach ($tdata['assertions'] as $ass) {
				echo " * {$ass['result']} {$ass['func_name']}(";
				echo implode(', ', $ass['func_args']);
				echo ") {$ass['msg']}\n";
			}
			echo "\n";
		}
		echo "TOTAL ASSERTIONS: {$total_assert_counts['pass']} pass, "
				. "{$total_assert_counts['fail']} fail, "
				. "{$total_assert_counts['total']} total\n";
		echo "TESTS: {$test_counts['run']} run, "
				. "{$test_counts['pass']} pass, "
				. "{$test_counts['total']} total\n";
	}

	public static function add_test($name, \Closure $test) {
		static::$tests[$name] = array(
			'run' => false,
			'pass' => false,
			'test' => $test,
			'expected' => 0,
			'assertions' => array(),
		);
	}


	public static function add_assertion_result($func_name, $func_args, $result, $msg = null) {
		$result = ($result) ? static::PASS : static::FAIL;
		static::$tests[static::$current_test_name]['assertions'][] = compact('func_name', 'func_args', 'result', 'msg');
	}


	public static function run_test($name) {
		echo "Running test '{$name}...'\n";

		// to associate the assertions in a test with the test,
		// we use this static var to avoid the need to for globals
		static::$current_test_name = $name;
		$test = static::$tests[$name]['test'];

		if (isset(static::$setup_func)) {
			$setup_func = static::$setup_func;
			$setup_func();
			unset($setup_func);
		}

		$test();

		if (isset(static::$teardown_func)) {
			$teardown_func = static::$teardown_func;
			$teardown_func();
			unset($teardown_func);
		}

		static::$current_test_name = null;
		static::$tests[$name]['run'] = true;

		$assert_counts = static::assert_counts($name);
		if ($assert_counts['pass'] === $assert_counts['total']) {
			static::$tests[$name]['pass'] = true;
		} else {
			static::$tests[$name]['pass'] = false;
		}
	}


	public static function run_tests() {
		foreach (static::$tests as $name => &$test) {
			static::run_test($name);
		}
	}

	public static function assert_counts($test_name = null) {

		$total = 0;
		$pass  = 0;
		$fail  = 0;

		$test_asserts = function($test_name, $assertions) use ($total, $pass, $fail) {

			$total = 0;
			$pass  = 0;
			$fail  = 0;

			foreach ($assertions as $ass) {
				if ($ass['result'] === fu::PASS) {
					$pass++;
				} elseif ($ass['result'] === fu::FAIL) {
					$fail++;
				}
				$total++;
			}

			return compact('total', 'pass', 'fail');

		};

		if ($test_name) {
			$assertions = static::$tests[$test_name]['assertions'];
			$rs = $test_asserts($test_name, $assertions);
			$total += $rs['total'];
			$pass += $rs['pass'];
			$fail += $rs['fail'];
		} else {
			foreach (static::$tests as $test_name => $tdata) {
				$assertions = static::$tests[$test_name]['assertions'];
				$rs = $test_asserts($test_name, $assertions);
				$total += $rs['total'];
				$pass += $rs['pass'];
				$fail += $rs['fail'];
			}
		}

		return compact('total', 'pass', 'fail');

	}


	public static function test_counts() {
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


	public static function setup(\Closure $setup) {
		static::$setup_func = $setup;
	}

	public static function teardown(\Closure $teardown) {
		static::$teardown_func = $teardown;
	}

	public static function test($name, \Closure $test) {
		static::add_test($name, $test);
	}

	public static function equal($a, $b, $msg = null) {
		$rs = ($a == $b);
		static::add_assertion_result(__FUNCTION__, array($a, $b), $rs, $msg);
	}

	public static function not_equal($a, $b, $msg = null) {
		$rs = ($a != $b);
		static::add_assertion_result(__FUNCTION__, array($a, $b), $rs, $msg);
	}

	public static function deep_equal($a, $b, $msg = null) {
		$rs = ($a === $b);
		static::add_assertion_result(__FUNCTION__, array($a, $b), $rs, $msg);
	}

	public static function not_deep_equal($a, $b, $msg = null) {
		$rs = ($a !== $b);
		static::add_assertion_result(__FUNCTION__, array($a, $b), $rs, $msg);
	}

	public static function ok($a, $msg = null) {
		$rs = (bool)$a;
		static::add_assertion_result(__FUNCTION__, array($a), $rs, $msg);
	}

	public static function run($report = true) {
		static::run_tests();
		if ($report) { static::report(); }
	}
	/**
	 * @TODO
	 */
	public function expect($int) {}
}
