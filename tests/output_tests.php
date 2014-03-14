<?php
use \FUnit as fu;
use \FUnit\TestSuite;

require_once __DIR__ . '/../src/FUnit.php';

fu::test('test output when FUnit::set_disable_reporting(true)', function () use ($report_disabled) {
    $report_disabled = shell_exec(
        "php " . __DIR__
        . "/output_scripts/set_disable_reporting_true.php"
    );
    fu::strict_equal(0, strlen($report_disabled), "no report output");
});

fu::test('test output when FUnit::set_disable_reporting(false)', function () use ($report_disabled) {
    $report_disabled = shell_exec(
        "php " . __DIR__
        . "/output_scripts/set_disable_reporting_false.php"
    );
    fu::strict_equal(0, strlen($report_disabled), "Report output received");
});

fu::run();
