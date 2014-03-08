<?php

namespace FUnit;

/**
 *
 */
class TestSuite
{
    public $tests = array();

    public $run = false;

    public $current_test_name = null;

    public $setup_func = null;

    public $teardown_func = null;

    public $fixtures = array();

    public $errors = array();

    public $exit_code = 0;

    public $name = \FUnit::DEFAULT_SUITE_NAME;

    public function __construct($name = \FUnit::DEFAULT_SUITE_NAME)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExitCode()
    {
        return $this->exit_code;
    }

    public function getTests()
    {
        return $this->tests;
    }

    /**
     * @see \FUnit::add_test()
     */
    public function addTest($name, \Closure $test)
    {
        \FUnit::debug_out("Adding test {$name} to suite " . $this->getName());
        $this->tests[$name] = array(
            'run' => false,
            'skipped' => false,
            'pass' => false,
            'test' => $test,
            'errors' => array(),
            'assertions' => array(),
        );

    }

    /**
     * @see \FUnit::setup()
     */
    public function setup(\Closure $setup)
    {
        $this->setup_func = $setup;
    }

    /**
     * @see \FUnit::teardown()
     */
    public function teardown(\Closure $teardown)
    {
        $this->teardown_func = $teardown;
    }

    /**
     * stuff that runs before the suite begins. Triggered by `run()`
     */
    public function before(\Closure $before)
    {
        $this->before_func = $before;
    }

    /**
     * stuff that runs after the suite begins. Triggered by `run()`
     */
    public function after(\Closure $after)
    {
        $this->after_func = $after;
    }

    public function fixture($key, $val = null)
    {
        if (isset($val)) {
            $this->fixtures[$key] = $val;
        }

        return $this->fixtures[$key];
    }

    public function resetFixtures()
    {
        $this->fixtures = array();
    }

    /**
     * @see \FUnit::add_error_data()
     */
    public function addErrorData($edata)
    {
        $this->errors[] = $edata;
        if ($this->current_test_name) {
            $this->tests[$this->current_test_name]['errors'][] = $edata;
        }
    }

    /**
     * @see \FUnit::add_assertion_result()
     */
    public function addAssertionResult($func_name, $func_args, $result, $msg = null, $expected_fail = false)
    {
        $result = ($result) ? \FUnit::PASS : \FUnit::FAIL;
        $this->tests[$this->current_test_name]['assertions'][] = compact('func_name', 'func_args', 'result', 'msg', 'expected_fail');
    }

    /**
     * @see \FUnit::run_test()
     * @return [type] [description]
     */
    public function runTest($name)
    {

        // don't run a test more than once!
        if ($this->tests[$name]['run']) {
            \FUnit::debug_out("test '{$name}' was already run; skipping");
            return $this->tests[$name];
        }

        \FUnit::info_out("Running test '{$name}...'");

        $ts_start = microtime(true);

        // to associate the assertions in a test with the test,
        // we use this static var to avoid the need to for globals
        $this->current_test_name = $name;
        $test = $this->tests[$name]['test'];

        // setup
        if (isset($this->setup_func)) {
            \FUnit::debug_out("running setup for '{$name}'");
            $setup_func = $this->setup_func;
            $setup_func();
            unset($setup_func);
        }
        $ts_setup = microtime(true);

        try {
            \FUnit::debug_out("executing test function for '{$name}'");
            $test();

        } catch(\Exception $e) {

            \FUnit::exception_handler($e);

        }
        $ts_run = microtime(true);

        // teardown
        if (isset($this->teardown_func)) {
            \FUnit::debug_out("running teardown for '{$name}'");
            $teardown_func = $this->teardown_func;
            $teardown_func();
            unset($teardown_func);
        }
        $ts_teardown = microtime(true);

        $this->current_test_name = null;
        $this->tests[$name]['run'] = true;
        $this->tests[$name]['timing'] = array(
            'setup' => $ts_setup - $ts_start,
            'run' => $ts_run - $ts_setup,
            'teardown' => $ts_teardown - $ts_run,
            'total' => $ts_teardown - $ts_start,
        );

        if (count($this->tests[$name]['errors']) > 0) {

            $this->tests[$name]['pass'] = false;

        } else {

            $assert_counts = $this->assertCounts($name);
            if ($assert_counts['pass'] === $assert_counts['total']) {
                $this->tests[$name]['pass'] = true;
            } else {
                $this->tests[$name]['pass'] = false;
            }
        }

        if (false === $this->tests[$name]['pass']){

            $this->exit_code = 1;

        }

        \FUnit::debug_out("Timing: " . json_encode($this->tests[$name]['timing'])); // json is easy to read

        return $this->tests[$name];

    }

    /**
     * @see \FUnit::run_tests()
     * @return [type] [description]
     */
    public function runTests($filter = null)
    {
        foreach ($this->tests as $name => &$test)  {
            if (is_null($filter) || stripos($name, $filter) !== false) {
                $this->runTest($name);
            } else {
                $this->tests[$name]['skipped'] = true;
                \FUnit::debug_out("skipping test {$name} due to filter");
            }
        }
        return $this->tests;
    }

    /**
     * @see \FUnit::assert_counts()
     * @return array
     */
    public function assertCounts($test_name = null)
    {

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
            $assertions = $this->tests[$test_name]['assertions'];
            $rs = $test_asserts($test_name, $assertions);
            $total += $rs['total'];
            $pass += $rs['pass'];
            $fail += $rs['fail'];
            $expected_fail += $rs['expected_fail'];
        } else {
            foreach ($this->tests as $test_name => $tdata) {
                $assertions = $this->tests[$test_name]['assertions'];
                $rs = $test_asserts($test_name, $assertions);
                $total += $rs['total'];
                $pass += $rs['pass'];
                $fail += $rs['fail'];
                $expected_fail += $rs['expected_fail'];
            }
        }

        return compact('total', 'pass', 'fail', 'expected_fail');

    }


    public function testCounts()
    {
        $total = count($this->tests);
        $run = 0;
        $pass = 0;

        foreach ($this->tests as $test_name => $tdata) {
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
     * runs the suite and returns the exit code
     * @param  string $filter
     * @return integer         exit code
     */
    public function run($filter = null)
    {

    }

}