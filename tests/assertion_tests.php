<?php

use \FUnit as fu;
use \FUnit\TestSuite;

require_once __DIR__ . '/../src/FUnit.php';

fu::suite('Assertion test suite');

fu::test('FUnit::assert_ok tests', function() {
    fu::strict_equal(true, fu::assert_ok(1), "1 is truthy");
    fu::strict_equal(false, fu::assert_ok(0), "0 is falsy");
    fu::strict_equal(false, fu::assert_ok(""), "empty string is falsy");
    fu::strict_equal(false, fu::assert_ok(array()), "empty array is falsy");
    fu::strict_equal(false, fu::assert_ok(null), "null is falsy");
    fu::strict_equal(false, fu::assert_ok(false), "false is falsy");
    fu::strict_equal(true, fu::assert_ok(true), "true is truthy");
    fu::strict_equal(true, fu::assert_ok('false'), "'false' is truthy");
    fu::strict_equal(true, fu::assert_ok(new stdClass), "stdClass is truthy");
});

fu::test('FUnit::assert_not_ok tests', function() {
    fu::strict_equal(false, fu::assert_not_ok(1), "1 is truthy");
    fu::strict_equal(true, fu::assert_not_ok(0), "0 is falsy");
    fu::strict_equal(true, fu::assert_not_ok(""), "empty string is falsy");
    fu::strict_equal(true, fu::assert_not_ok(array()), "empty array is falsy");
    fu::strict_equal(true, fu::assert_not_ok(null), "null is falsy");
    fu::strict_equal(true, fu::assert_not_ok(false), "false is falsy");
    fu::strict_equal(false, fu::assert_not_ok(true), "true is truthy");
    fu::strict_equal(false, fu::assert_not_ok('false'), "'false' is truthy");
    fu::strict_equal(false, fu::assert_not_ok(new stdClass), "stdClass is truthy");
});

fu::test('FUnit::assert_all_ok tests', function() {
    $all_ints = array(1, 2, 3, 4, 5);
    $not_all_ints = array(1, 2, "3", 4, 5);

    fu::strict_equal(true, fu::assert_all_ok($all_ints, function($val) {
        return is_int($val);
    }), "\$all_ints are all integers");

    fu::strict_equal(false, fu::assert_all_ok($not_all_ints, function($val) {
        return is_int($val);
    }), "\$not_all_ints are NOT all integers");
});

fu::run();