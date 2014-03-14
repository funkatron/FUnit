<?php

/**
 * you should run this standalone (not with the test runner)
 */

use \FUnit as fu;
use \FUnit\TestSuite;

require_once __DIR__ . '/../../src/FUnit.php';

fu::test('FUnit::set_disable_reporting(true)', function () {
    fu::set_disable_reporting(true);
    fu::equal(true, fu::$disable_reporting, "\$disable_reporting is true");
});

fu::set_silence(true);
fu::set_disable_reporting(true);

fu::run();
