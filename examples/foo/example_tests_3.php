<?php
use \FUnit as fu;

fu::setup(function() {
    // set a fixture to use in tests
    fu::fixture('foobar', array('foo'=>'bar'));
});

fu::teardown(function() {
    // this resets the fu::$fixtures array. May not provide clean shutdown
    fu::reset_fixtures();
});

fu::test("test for PHP 5.3 or above", function () {
    fu::strict_equal(true, version_compare(PHP_VERSION, '5.3.0') >= 0, "current PHP is >= 5.3.0");
});

fu::run();
