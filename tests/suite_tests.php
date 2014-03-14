<?php

use \FUnit as fu;
use \FUnit\TestSuite;

require_once __DIR__ . '/../src/FUnit.php';

fu::suite('Test suite tests');

fu::setup(function () {
    fu::fixture('ts', new TestSuite('Fixture Suite'));
});

fu::teardown(function () {
    fu::reset_fixtures();
});

fu::test("Check suite name", function () {
    $ts = fu::fixture('ts');
    fu::strict_equal('Fixture Suite', $ts->getName());
});

fu::test("Check suite run state", function () {
    $ts = fu::fixture('ts');
    fu::strict_equal(false, $ts->run);
    $ts->run();
    fu::strict_equal(true, $ts->run);
});

fu::test("Check suite exit code 1", function () {
    $ts = fu::fixture('ts');
    fu::strict_equal(0, $ts->getExitCode());
    $ts->addTest('known to fail for suite', function () use ($ts) {
        // this forces the result of this assertion to be recorded in
        // the `$ts` TestSuite instance
        fu::fail($ts, 'this always fails');
    });
    $ts->run();
    fu::strict_equal(1, $ts->getExitCode());
});

fu::test("Check suite exit code 0", function () {
    $ts = fu::fixture('ts');
    fu::strict_equal(0, $ts->getExitCode());
    $ts->addTest('known to fail for suite', function () use ($ts) {
        // this forces the result of this assertion to be recorded in
        // the `$ts` TestSuite instance
        fu::pass($ts, 'this always fails');
    });
    $ts->run();
    fu::strict_equal(0, $ts->getExitCode());
});

fu::run();
