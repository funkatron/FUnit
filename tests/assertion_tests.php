<?php

use \FUnit as fu;
use \FUnit\TestSuite;

require_once __DIR__ . '/../src/FUnit.php';

fu::suite('Assertion test suite');

fu::test('FUnit::assert_ok tests', function () {
    fu::strict_equal(true, fu::assert_ok(1)['result'], "1 is truthy");
    fu::strict_equal(false, fu::assert_ok(0)['result'], "0 is falsy");
    fu::strict_equal(false, fu::assert_ok("")['result'], "empty string is falsy");
    fu::strict_equal(false, fu::assert_ok(array())['result'], "empty array is falsy");
    fu::strict_equal(false, fu::assert_ok(null)['result'], "null is falsy");
    fu::strict_equal(false, fu::assert_ok(false)['result'], "false is falsy");
    fu::strict_equal(true, fu::assert_ok(true)['result'], "true is truthy");
    fu::strict_equal(true, fu::assert_ok('false')['result'], "'false' is truthy");
    fu::strict_equal(true, fu::assert_ok(new stdClass)['result'], "stdClass is truthy");
});

fu::test('FUnit::assert_not_ok tests', function () {
    fu::strict_equal(false, fu::assert_not_ok(1)['result'], "1 is truthy");
    fu::strict_equal(true, fu::assert_not_ok(0)['result'], "0 is falsy");
    fu::strict_equal(true, fu::assert_not_ok("")['result'], "empty string is falsy");
    fu::strict_equal(true, fu::assert_not_ok(array())['result'], "empty array is falsy");
    fu::strict_equal(true, fu::assert_not_ok(null)['result'], "null is falsy");
    fu::strict_equal(true, fu::assert_not_ok(false)['result'], "false is falsy");
    fu::strict_equal(false, fu::assert_not_ok(true)['result'], "true is truthy");
    fu::strict_equal(false, fu::assert_not_ok('false')['result'], "'false' is truthy");
    fu::strict_equal(false, fu::assert_not_ok(new stdClass)['result'], "stdClass is truthy");
});

fu::test('FUnit::assert_all_ok tests', function () {
    $all_ints = array(1, 2, 3, 4, 5);
    $not_all_ints = array(1, 2, "3", 4, 5);

    fu::strict_equal(true, fu::assert_all_ok($all_ints, function ($val) {
        return is_int($val);
    })['result'], "\$all_ints are all integers");

    fu::strict_equal(false, fu::assert_all_ok($not_all_ints, function ($val) {
        return is_int($val);
    })['result'], "\$not_all_ints are NOT all integers");
});


fu::test('FUnit::assert_equal tests', function () {
    fu::strict_equal(true, fu::assert_equal(1, 1)['result'], "1 and 1 are 'equal'");
    fu::strict_equal(true, fu::assert_equal("a", "a")['result'], "'a' and 'a' are 'equal'");
    fu::strict_equal(true, fu::assert_equal(new stdClass, new stdClass)['result'], "new stdClass and new stdClass are 'equal'");
    fu::strict_equal(true, fu::assert_equal(1, "1")['result'], "1 and '1' are 'equal'");
    fu::strict_equal(true, fu::assert_equal(1, true)['result'], "1 and true are 'equal'");
    fu::strict_equal(true, fu::assert_equal(null, 0)['result'], "null and 0 are 'equal'");
    fu::strict_equal(true, fu::assert_equal(false, null)['result'], "false and null are 'equal'");
    fu::strict_equal(true, fu::assert_equal(array(), null)['result'], "array() and null are 'equal'");
    fu::strict_equal(false, fu::assert_equal(array(), 1)['result'], "array() and 1 are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(1, 0)['result'], "1 and 1 are not 'equal'");
    fu::strict_equal(false, fu::assert_equal("a", "b")['result'], "'a' and 'b' are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(new stdClass, new ArrayObject)['result'], "new stdClass and new ArrayObject are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(1, "a")['result'], "1 and 'a' are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(0, true)['result'], "0 and true are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(null, 1)['result'], "null and 1 are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(true, null)['result'], "false and null are not 'equal'");
    fu::strict_equal(false, fu::assert_equal(array('0'), null)['result'], "array('0') and null are not 'equal'");
});

fu::test('FUnit::assert_not_equal tests', function () {
    fu::strict_equal(false, fu::assert_not_equal(1, 1)['result'], "1 and 1 are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal("a", "a")['result'], "'a' and 'a' are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(new stdClass, new stdClass)['result'], "new stdClass and new stdClass are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(1, "1")['result'], "1 and '1' are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(1, true)['result'], "1 and true are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(null, 0)['result'], "null and 0 are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(false, null)['result'], "false and null are 'equal'");
    fu::strict_equal(false, fu::assert_not_equal(array(), null)['result'], "array() and null are 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(array(), 1)['result'], "array() and 1 are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(1, 0)['result'], "1 and 1 are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal("a", "b")['result'], "'a' and 'b' are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(new stdClass, new ArrayObject)['result'], "new stdClass and new ArrayObject are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(1, "a")['result'], "1 and 'a' are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(0, true)['result'], "0 and true are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(null, 1)['result'], "null and 1 are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(true, null)['result'], "false and null are not 'equal'");
    fu::strict_equal(true, fu::assert_not_equal(array('0'), null)['result'], "array('0') and null are not 'equal'");
});

fu::test('FUnit::assert_strict_equal tests', function () {
    fu::strict_equal(true, fu::assert_strict_equal(1, 1)['result'], "1 and 1 are strict equal");
    fu::strict_equal(true, fu::assert_strict_equal("a", "a")['result'], "'a' and 'a' are strict equal");
    $a = new StdClass;
    $b = $a;
    fu::strict_equal(true, fu::assert_strict_equal($a, $b)['result'], "\$a and \$b refer to same object, so strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(new stdClass, new stdClass)['result'], "new stdClass and new stdClass are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(1, "1")['result'], "1 and '1' are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(1, true)['result'], "1 and true are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(null, 0)['result'], "null and 0 are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(false, null)['result'], "false and null are not strict equal");
    fu::strict_equal(false, fu::assert_strict_equal(array(), null)['result'], "array() and null are not strict equal");
});

fu::test('FUnit::assert_not_strict_equal tests', function () {
    fu::strict_equal(false, fu::assert_not_strict_equal(1, 1)['result'], "1 and 1 are strict equal");
    fu::strict_equal(false, fu::assert_not_strict_equal("a", "a")['result'], "'a' and 'a' are strict equal");
    $a = new StdClass;
    $b = $a;
    fu::strict_equal(false, fu::assert_not_strict_equal($a, $b)['result'], "\$a and \$b refer to same object, so strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(new stdClass, new stdClass)['result'], "new stdClass and new stdClass are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(1, "1")['result'], "1 and '1' are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(1, true)['result'], "1 and true are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(null, 0)['result'], "null and 0 are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(false, null)['result'], "false and null are not strict equal");
    fu::strict_equal(true, fu::assert_not_strict_equal(array(), null)['result'], "array() and null are not strict equal");
});

fu::test('FUnit::assert_throws tests', function () {
    $callback = function () {
        throw new RuntimeException();
    };
    $rs = fu::assert_throws($callback, 'RuntimeException')['result'];
    fu::strict_equal(true, $rs, "callback threw correct exception type");

    $callback = function ($foo) {
        throw new RuntimeException($foo);
    };
    $rs = fu::assert_throws($callback, array('bar'), 'LogicException')['result'];
    fu::strict_equal(false, $rs, "callback didn't throw correct exception type");
});

fu::test('FUnit::assert_has tests', function () {

    $arr = array(
                 "foo" => true,
                 "bar" => null,
                 "baz" => "bingo",
                 );

    $obj = new stdClass;
    $obj->foo = true;
    $obj->bar = null;
    $obj->baz = "bingo";

    fu::strict_equal(true, fu::assert_has('foo', $arr)['result'], "\$arr has key 'foo'");
    fu::strict_equal(true, fu::assert_has('bar', $arr)['result'], "\$arr has key 'bar'");
    fu::strict_equal(true, fu::assert_has('baz', $arr)['result'], "\$arr has key 'baz'");
    fu::strict_equal(false, fu::assert_has('bingo', $arr)['result'], "\$arr does not have key 'bingo'");

    fu::strict_equal(true, fu::assert_has('foo', $obj)['result'], "\$obj has property 'foo'");
    fu::strict_equal(true, fu::assert_has('bar', $obj)['result'], "\$obj has property 'bar'");
    fu::strict_equal(true, fu::assert_has('baz', $obj)['result'], "\$obj has property 'baz'");
    fu::strict_equal(false, fu::assert_has('bingo', $obj)['result'], "\$obj does not have property 'bingo'");

});


fu::test('FUnit::assert_not_has tests', function () {
    $arr = array(
                 "foo" => true,
                 "bar" => null,
                 "baz" => "bingo",
                 );

    $obj = new stdClass;
    $obj->foo = true;
    $obj->bar = null;
    $obj->baz = "bingo";

    fu::strict_equal(false, fu::assert_not_has('foo', $arr)['result'], "\$arr has key 'foo'");
    fu::strict_equal(false, fu::assert_not_has('bar', $arr)['result'], "\$arr has key 'bar'");
    fu::strict_equal(false, fu::assert_not_has('baz', $arr)['result'], "\$arr has key 'baz'");
    fu::strict_equal(true, fu::assert_not_has('bingo', $arr)['result'], "\$arr does not have key 'bingo'");

    fu::strict_equal(false, fu::assert_not_has('foo', $obj)['result'], "\$obj has property 'foo'");
    fu::strict_equal(false, fu::assert_not_has('bar', $obj)['result'], "\$obj has property 'bar'");
    fu::strict_equal(false, fu::assert_not_has('baz', $obj)['result'], "\$obj has property 'baz'");
    fu::strict_equal(true, fu::assert_not_has('bingo', $obj)['result'], "\$obj does not have property 'bingo'");
});


fu::test('FUnit::assert_fail tests', function () {
    fu::strict_equal(false, fu::assert_fail()['result'], "forced fail");
});
fu::test('FUnit::assert_expect_fail tests', function () {
    fu::strict_equal(false, fu::assert_expect_fail()['result'], "forced expected fail");
});
fu::test('FUnit::assert_pass tests', function () {
    fu::strict_equal(true, fu::assert_pass()['result'], "forced pass");
});

fu::run();
