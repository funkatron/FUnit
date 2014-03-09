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


fu::test('FUnit::assert_equal tests', function() {
    fu::strict_equal(true, fu::assert_equal(1, 1), "1 and 1 are 'equal'");
    fu::strict_equal(true, fu::assert_equal("a", "a"), "'a' and 'a' are 'equal'");
    fu::strict_equal(true, fu::assert_equal(new stdClass, new stdClass), "new stdClass and new stdClass are 'equal'");
    fu::strict_equal(true, fu::assert_equal(1, "1"), "1 and '1' are 'equal'");
    fu::strict_equal(true, fu::assert_equal(1, true), "1 and true are 'equal'");
    fu::strict_equal(true, fu::assert_equal(null, 0), "null and 0 are 'equal'");
    fu::strict_equal(true, fu::assert_equal(false, null), "false and null are 'equal'");
    fu::strict_equal(true, fu::assert_equal(array(), null), "array() and null are 'equal'");
    fu::strict_equal(false, fu::assert_equal(array(), 1), "array() and 1 are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(1, 0), "1 and 1 are not 'equal'");
    fu::strict_equal(false, fu::assert_equal("a", "b"), "'a' and 'b' are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(new stdClass, new ArrayObject), "new stdClass and new ArrayObject are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(1, "a"), "1 and 'a' are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(0, true), "0 and true are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(null, 1), "null and 1 are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(true, null), "false and null are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(array('0'), null), "array('0') and null are not 'equal'");
});

fu::test('FUnit::assert_not_equal tests', function() {
    fu::strict_equal(false, fu::assert_not_equal(1, 1), "1 and 1 are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal("a", "a"), "'a' and 'a' are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(new stdClass, new stdClass), "new stdClass and new stdClass are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(1, "1"), "1 and '1' are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(1, true), "1 and true are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(null, 0), "null and 0 are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(false, null), "false and null are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(array(), null), "array() and null are 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(array(), 1), "array() and 1 are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(1, 0), "1 and 1 are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal("a", "b"), "'a' and 'b' are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(new stdClass, new ArrayObject), "new stdClass and new ArrayObject are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(1, "a"), "1 and 'a' are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(0, true), "0 and true are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(null, 1), "null and 1 are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(true, null), "false and null are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(array('0'), null), "array('0') and null are not 'equal'");
});

fu::test('FUnit::assert_strict_equal tests', function() {
    fu::strict_equal(true, fu::assert_strict_equal(1, 1), "1 and 1 are strict equal");
    fu::strict_equal(true, fu::assert_strict_equal("a", "a"), "'a' and 'a' are strict equal");
    $a = new StdClass;
    $b = $a;
    fu::strict_equal(true, fu::assert_strict_equal($a, $b), "\$a and \$b refer to same object, so strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(new stdClass, new stdClass), "new stdClass and new stdClass are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(1, "1"), "1 and '1' are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(1, true), "1 and true are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(null, 0), "null and 0 are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(false, null), "false and null are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(array(), null), "array() and null are not strict equal");
});

fu::test('FUnit::assert_not_strict_equal tests', function() {
    fu::strict_equal(false, fu::assert_not_strict_equal(1, 1), "1 and 1 are strict equal");
    fu::strict_equal(false, fu::assert_not_strict_equal("a", "a"), "'a' and 'a' are strict equal");
    $a = new StdClass;
    $b = $a;
    fu::strict_equal(false, fu::assert_not_strict_equal($a, $b), "\$a and \$b refer to same object, so strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(new stdClass, new stdClass), "new stdClass and new stdClass are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(1, "1"), "1 and '1' are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(1, true), "1 and true are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(null, 0), "null and 0 are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(false, null), "false and null are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(array(), null), "array() and null are not strict equal");
});

fu::test('FUnit::assert_throws tests', function() {
    $callback = function() { throw new RuntimeException(); };
    $rs = fu::assert_throws($callback, 'RuntimeException');
    fu::strict_equal(true, $rs, "callback threw correct exception type");

    $callback = function($foo) { throw new RuntimeException($foo); };
    $rs = fu::assert_throws($callback, array('bar'), 'LogicException');
    fu::strict_equal(false, $rs, "callback didn't throw correct exception type");
});

fu::test('FUnit::assert_has tests', function() {

    $arr = array(
                 "foo" => true,
                 "bar" => null,
                 "baz" => "bingo",
                 );

    $obj = new stdClass;
    $obj->foo = true;
    $obj->bar = null;
    $obj->baz = "bingo";

    fu::strict_equal(true, fu::assert_has('foo', $arr), "\$arr has key 'foo'");
    fu::strict_equal(true, fu::assert_has('bar', $arr), "\$arr has key 'bar'");
    fu::strict_equal(true, fu::assert_has('baz', $arr), "\$arr has key 'baz'");
    fu::strict_equal(false, fu::assert_has('bingo', $arr), "\$arr does not have key 'bingo'");

    fu::strict_equal(true, fu::assert_has('foo', $obj), "\$obj has property 'foo'");
    fu::strict_equal(true, fu::assert_has('bar', $obj), "\$obj has property 'bar'");
    fu::strict_equal(true, fu::assert_has('baz', $obj), "\$obj has property 'baz'");
    fu::strict_equal(false, fu::assert_has('bingo', $obj), "\$obj does not have property 'bingo'");

});


fu::test('FUnit::assert_not_has tests', function() {
    $arr = array(
                 "foo" => true,
                 "bar" => null,
                 "baz" => "bingo",
                 );

    $obj = new stdClass;
    $obj->foo = true;
    $obj->bar = null;
    $obj->baz = "bingo";

    fu::strict_equal(false, fu::assert_not_has('foo', $arr), "\$arr has key 'foo'");
    fu::strict_equal(false, fu::assert_not_has('bar', $arr), "\$arr has key 'bar'");
    fu::strict_equal(false, fu::assert_not_has('baz', $arr), "\$arr has key 'baz'");
    fu::strict_equal(true, fu::assert_not_has('bingo', $arr), "\$arr does not have key 'bingo'");

    fu::strict_equal(false, fu::assert_not_has('foo', $obj), "\$obj has property 'foo'");
    fu::strict_equal(false, fu::assert_not_has('bar', $obj), "\$obj has property 'bar'");
    fu::strict_equal(false, fu::assert_not_has('baz', $obj), "\$obj has property 'baz'");
    fu::strict_equal(true, fu::assert_not_has('bingo', $obj), "\$obj does not have property 'bingo'");
});


fu::test('FUnit::assert_fail tests', function() {
    fu::strict_equal(false, fu::assert_fail(), "forced fail");
});
fu::test('FUnit::assert_expect_fail tests', function() {
    fu::strict_equal(false, fu::assert_expect_fail(), "forced expected fail");
});
fu::test('FUnit::assert_pass tests', function() {
    fu::strict_equal(true, fu::assert_pass(), "forced pass");
});

fu::run();