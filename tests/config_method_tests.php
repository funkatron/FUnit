<?php

/**
 * you should run this standalone (not with the test runner)
 */

use \FUnit as fu;
use \FUnit\TestSuite;

require_once __DIR__ . '/../src/FUnit.php';

fu::test('FUnit::set_disable_reporting(true)', function() {
    fu::set_disable_reporting(true);
    fu::equal(true, fu::$disable_reporting, "\$disable_reporting is true");
});

fu::set_silence(true);
fu::set_disable_reporting(true);

ob_start();
fu::run();
$report_disabled = ob_get_clean();

fu::test('test output when FUnit::set_disable_reporting(true)', function() use ($report_disabled) {
    fu::strict_equal(0, strlen($report_disabled), "no report output");
});

fu::run(false);


///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

fu::test('FUnit::set_disable_reporting(false)', function() {
    fu::set_disable_reporting(false);
    fu::equal(false, fu::$disable_reporting, "\$disable_reporting is false");
});

fu::set_silence(true);
fu::set_disable_reporting(false);

ob_start();
fu::run();
$report_enabled = ob_get_clean();

fu::test('test output when FUnit::set_disable_reporting(false)', function() use ($report_enabled) {
    fu::ok(0 < strlen($report_enabled), "report was output");
});

fu::run(false);

fu::report('text', fu::$all_run_tests);
