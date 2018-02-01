<?php

use \FUnit as fu;
use \FUnit\TestSuite;

require_once __DIR__ . '/../src/FUnit.php';

fu::suite('Assertion test suite');

fu::test('FUnit::assert_ok tests', function () {
    $assert = fu::assert_ok(1);
    fu::strict_equal(true, $assert['result'], "1 is truthy");
    $assert = fu::assert_ok(0);
    fu::strict_equal(false, $assert['result'], "0 is falsy");
    $assert = fu::assert_ok("");
    fu::strict_equal(false, $assert['result'], "empty string is falsy");
    $assert = fu::assert_ok(array());
    fu::strict_equal(false, $assert['result'], "empty array is falsy");
    $assert = fu::assert_ok(null);
    fu::strict_equal(false, $assert['result'], "null is falsy");
    $assert = fu::assert_ok(false);
    fu::strict_equal(false, $assert['result'], "false is falsy");
    $assert = fu::assert_ok(true);
    fu::strict_equal(true, $assert['result'], "true is truthy");
    $assert = fu::assert_ok('false');
    fu::strict_equal(true, $assert['result'], "'false' is truthy");
    $assert = fu::assert_ok(new stdClass);
    fu::strict_equal(true, $assert['result'], "stdClass is truthy");
});

fu::test('FUnit::assert_not_ok tests', function () {
    $assert = fu::assert_not_ok(1);
    fu::strict_equal(false, $assert['result'], "1 is truthy");
    $assert = fu::assert_not_ok(0);
    fu::strict_equal(true, $assert['result'], "0 is falsy");
    $assert = fu::assert_not_ok("");
    fu::strict_equal(true, $assert['result'], "empty string is falsy");
    $assert = fu::assert_not_ok(array());
    fu::strict_equal(true, $assert['result'], "empty array is falsy");
    $assert = fu::assert_not_ok(null);
    fu::strict_equal(true, $assert['result'], "null is falsy");
    $assert = fu::assert_not_ok(false);
    fu::strict_equal(true, $assert['result'], "false is falsy");
    $assert = fu::assert_not_ok(true);
    fu::strict_equal(false, $assert['result'], "true is truthy");
    $assert = fu::assert_not_ok('false');
    fu::strict_equal(false, $assert['result'], "'false' is truthy");
    $assert = fu::assert_not_ok(new stdClass);
    fu::strict_equal(false, $assert['result'], "stdClass is truthy");
});

fu::test('FUnit::assert_all_ok tests', function () {
    $all_ints = array(1, 2, 3, 4, 5);
    $not_all_ints = array(1, 2, "3", 4, 5);

    $assert = fu::assert_all_ok($all_ints, function ($val) {
        return is_int($val);
    });
    fu::strict_equal(true, $assert['result'], "\$all_ints are all integers");

    $assert = fu::assert_all_ok($not_all_ints, function ($val) {
        return is_int($val);
    });
    fu::strict_equal(false, $assert['result'], "\$not_all_ints are NOT all integers");

    try {
        fu::assert_all_ok($all_ints, 'not callable');
    } catch (\Exception $e) {
        fu::ok(
            $e instanceof \InvalidArgumentException,
            'throws InvalidArgumentException if no valid callback is provided'
        );
    }
});


fu::test('FUnit::assert_equal tests', function () {
    $assert = fu::assert_equal(1, 1);
    fu::strict_equal(true, $assert['result'], "1 and 1 are 'equal'");
    $assert = fu::assert_equal("a", "a");
    fu::strict_equal(true, $assert['result'], "'a' and 'a' are 'equal'");
    $assert = fu::assert_equal(new stdClass, new stdClass);
    fu::strict_equal(true, $assert['result'], "new stdClass and new stdClass are 'equal'");
    $assert = fu::assert_equal(1, "1");
    fu::strict_equal(true, $assert['result'], "1 and '1' are 'equal'");
    $assert = fu::assert_equal(1, true);
    fu::strict_equal(true, $assert['result'], "1 and true are 'equal'");
    $assert = fu::assert_equal(null, 0);
    fu::strict_equal(true, $assert['result'], "null and 0 are 'equal'");
    $assert = fu::assert_equal(false, null);
    fu::strict_equal(true, $assert['result'], "false and null are 'equal'");
    $assert = fu::assert_equal(array(), null);
    fu::strict_equal(true, $assert['result'], "array() and null are 'equal'");
    $assert = fu::assert_equal(array(), 1);
    fu::strict_equal(false, $assert['result'], "array() and 1 are not 'equal'");
    $assert = fu::assert_equal(1, 0);
    fu::strict_equal(false, $assert['result'], "'a' and 'b' are not 'equal'");
    $assert = fu::assert_equal("a", "b");
    fu::strict_equal(false, $assert['result'], "new stdClass and new ArrayObject are not 'equal'");
    $assert = fu::assert_equal(new stdClass, new ArrayObject);
    fu::strict_equal(false, $assert['result'], "1 and 'a' are not 'equal'");
    $assert = fu::assert_equal(1, "a");
    fu::strict_equal(false, $assert['result'], "0 and true are not 'equal'");
    $assert = fu::assert_equal(0, true);
    fu::strict_equal(false, $assert['result'], "null and 1 are not 'equal'");
    $assert = fu::assert_equal(null, 1);
    fu::strict_equal(false, $assert['result'], "false and null are not 'equal'");
    $assert = fu::assert_equal(true, null);
    fu::strict_equal(false, $assert['result'], "array('0') and null are not 'equal'");
    $assert = fu::assert_equal(array('0'), null);
});

fu::test('FUnit::assert_not_equal tests', function () {
    $assert = fu::assert_not_equal(1, 1);
    fu::strict_equal(false, $assert['result'], "1 and 1 are 'equal'");
    $assert = fu::assert_not_equal("a", "a");
    fu::strict_equal(false, $assert['result'], "'a' and 'a' are 'equal'");
    $assert = fu::assert_not_equal(new stdClass, new stdClass);
    fu::strict_equal(false, $assert['result'], "new stdClass and new stdClass are 'equal'");
    $assert = fu::assert_not_equal(1, "1");
    fu::strict_equal(false, $assert['result'], "1 and '1' are 'equal'");
    $assert = fu::assert_not_equal(1, true);
    fu::strict_equal(false, $assert['result'], "1 and true are 'equal'");
    $assert = fu::assert_not_equal(null, 0);
    fu::strict_equal(false, $assert['result'], "null and 0 are 'equal'");
    $assert = fu::assert_not_equal(false, null);
    fu::strict_equal(false, $assert['result'], "false and null are 'equal'");
    $assert = fu::assert_not_equal(array(), null);
    fu::strict_equal(false, $assert['result'], "array() and null are 'equal'");
    $assert = fu::assert_not_equal(array(), 1);
    fu::strict_equal(true, $assert['result'], "array() and 1 are not 'equal'");
    $assert = fu::assert_not_equal(1, 0);
    fu::strict_equal(true, $assert['result'], "1 and 1 are not 'equal'");
    $assert = fu::assert_not_equal("a", "b");
    fu::strict_equal(true, $assert['result'], "'a' and 'b' are not 'equal'");
    $assert = fu::assert_not_equal(new stdClass, new ArrayObject);
    fu::strict_equal(true, $assert['result'], "new stdClass and new ArrayObject are not 'equal'");
    $assert = fu::assert_not_equal(1, "a");
    fu::strict_equal(true, $assert['result'], "1 and 'a' are not 'equal'");
    $assert = fu::assert_not_equal(0, true);
    fu::strict_equal(true, $assert['result'], "0 and true are not 'equal'");
    $assert = fu::assert_not_equal(null, 1);
    fu::strict_equal(true, $assert['result'], "null and 1 are not 'equal'");
    $assert = fu::assert_not_equal(true, null);
    fu::strict_equal(true, $assert['result'], "false and null are not 'equal'");
    $assert = fu::assert_not_equal(array('0'), null);
    fu::strict_equal(true, $assert['result'], "array('0') and null are not 'equal'");
});

fu::test('FUnit::assert_strict_equal tests', function () {
    $assert = fu::assert_strict_equal(1, 1);
    fu::strict_equal(true, $assert['result'], "1 and 1 are strict equal");
    $assert = fu::assert_strict_equal("a", "a");
    fu::strict_equal(true, $assert['result'], "'a' and 'a' are strict equal");
    $a = new StdClass;
    $b = $a;
    $assert = fu::assert_strict_equal($a, $b);
    fu::strict_equal(true, $assert['result'], "\$a and \$b refer to same object, so strict equal");
    $assert = fu::assert_strict_equal(new stdClass, new stdClass);
    fu::strict_equal(false, $assert['result'], "new stdClass and new stdClass are not strict equal");
    $assert = fu::assert_strict_equal(1, "1");
    fu::strict_equal(false, $assert['result'], "1 and '1' are not strict equal");
    $assert = fu::assert_strict_equal(1, true);
    fu::strict_equal(false, $assert['result'], "1 and true are not strict equal");
    $assert = fu::assert_strict_equal(null, 0);
    fu::strict_equal(false, $assert['result'], "null and 0 are not strict equal");
    $assert = fu::assert_strict_equal(false, null);
    fu::strict_equal(false, $assert['result'], "false and null are not strict equal");
    $assert = fu::assert_strict_equal(array(), null);
    fu::strict_equal(false, $assert['result'], "array() and null are not strict equal");
});

fu::test('FUnit::assert_not_strict_equal tests', function () {
    $assert = fu::assert_not_strict_equal(1, 1);
    fu::strict_equal(false, $assert['result'], "1 and 1 are strict equal");
    $assert = fu::assert_not_strict_equal("a", "a");
    fu::strict_equal(false, $assert['result'], "'a' and 'a' are strict equal");
    $a = new StdClass;
    $b = $a;
    $assert = fu::assert_not_strict_equal($a, $b);
    fu::strict_equal(false, $assert['result'], "\$a and \$b refer to same object, so strict equal");
    $assert = fu::assert_not_strict_equal(new stdClass, new stdClass);
    fu::strict_equal(true, $assert['result'], "new stdClass and new stdClass are not strict equal");
    $assert = fu::assert_not_strict_equal(1, "1");
    fu::strict_equal(true, $assert['result'], "1 and '1' are not strict equal");
    $assert = fu::assert_not_strict_equal(1, true);
    fu::strict_equal(true, $assert['result'], "1 and true are not strict equal");
    $assert = fu::assert_not_strict_equal(null, 0);
    fu::strict_equal(true, $assert['result'], "null and 0 are not strict equal");
    $assert = fu::assert_not_strict_equal(false, null);
    fu::strict_equal(true, $assert['result'], "false and null are not strict equal");
    $assert = fu::assert_not_strict_equal(array(), null);
    fu::strict_equal(true, $assert['result'], "array() and null are not strict equal");
});

fu::test('FUnit::assert_throws tests', function () {
    $callback = function () {
        throw new RuntimeException();
    };
    $assert = fu::assert_throws($callback, 'RuntimeException');
    $rs = $assert['result'];
    fu::strict_equal(true, $rs, "callback threw correct exception type");

    $callback = function ($foo) {
        throw new RuntimeException($foo);
    };
    $assert = fu::assert_throws($callback, array('bar'), 'LogicException');
    $rs = $assert['result'];
    fu::strict_equal(false, $rs, "callback didn't throw correct exception type");
    
    try {
        fu::assert_throws('not callable', array());
    } catch (\Exception $e) {
        fu::ok(
            $e instanceof \InvalidArgumentException,
            'throws InvalidArgumentException if no valid callback is provided'
        );
    }
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

    $assert = fu::assert_has('foo', $arr);
    fu::strict_equal(true, $assert['result'], "\$arr has key 'foo'");
    $assert = fu::assert_has('bar', $arr);
    fu::strict_equal(true, $assert['result'], "\$arr has key 'bar'");
    $assert = fu::assert_has('baz', $arr);
    fu::strict_equal(true, $assert['result'], "\$arr has key 'baz'");
    $assert = fu::assert_has('bingo', $arr);
    fu::strict_equal(false, $assert['result'], "\$arr does not have key 'bingo'");

    $assert = fu::assert_has('foo', $obj);
    fu::strict_equal(true, $assert['result'], "\$obj has property 'foo'");
    $assert = fu::assert_has('bar', $obj);
    fu::strict_equal(true, $assert['result'], "\$obj has property 'bar'");
    $assert = fu::assert_has('baz', $obj);
    fu::strict_equal(true, $assert['result'], "\$obj has property 'baz'");
    $assert = fu::assert_has('bingo', $obj);
    fu::strict_equal(false, $assert['result'], "\$obj does not have property 'bingo'");

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

    $assert = fu::assert_not_has('foo', $arr);
    fu::strict_equal(false, $assert['result'], "\$arr has key 'foo'");
    $assert = fu::assert_not_has('bar', $arr);    
    fu::strict_equal(false, $assert['result'], "\$arr has key 'bar'");
    $assert = fu::assert_not_has('baz', $arr);    
    fu::strict_equal(false, $assert['result'], "\$arr has key 'baz'");
    $assert = fu::assert_not_has('bingo', $arr);    
    fu::strict_equal(true, $assert['result'], "\$arr does not have key 'bingo'");

    $assert = fu::assert_not_has('foo', $obj);
    fu::strict_equal(false, $assert['result'], "\$obj has property 'foo'");
    $assert = fu::assert_not_has('bar', $obj);    
    fu::strict_equal(false, $assert['result'], "\$obj has property 'bar'");
    $assert = fu::assert_not_has('baz', $obj);    
    fu::strict_equal(false, $assert['result'], "\$obj has property 'baz'");
    $assert = fu::assert_not_has('bingo', $obj);    
    fu::strict_equal(true, $assert['result'], "\$obj does not have property 'bingo'");
});


fu::test('FUnit::assert_fail tests', function () {
    $assert = fu::assert_fail();
    fu::strict_equal(false, $assert['result'], "forced fail");
});
fu::test('FUnit::assert_expect_fail tests', function () {
    $assert = fu::assert_expect_fail();
    fu::strict_equal(false, $assert['result'], "forced expected fail");
});
fu::test('FUnit::assert_pass tests', function () {
    $assert = fu::assert_pass();
    fu::strict_equal(true, $assert['result'], "forced pass");
});

fu::test('Ensure not including msg param has no side effects', function () {
    $assert = fu::assert_equal(1, 1, 'poop');
    fu::strict_equal(true, $assert['result']);
    $assert = fu::assert_equal(1, 1);
    fu::strict_equal(true, $assert['result']);
});


fu::run();
