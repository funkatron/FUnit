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

fu::test("this is a test", function() {
    fu::ok(1, "the integer '1' is okay");
    fu::ok(0, "the integer '0' is not okay"); // this will fail!
});

fu::test("another test", function() {
    fu::equal(true, 1, "the integer '1' is truthy");
    fu::not_strict_equal(true, 1, "the integer '1' is NOT true");
    // access a fixture
    $foobar = fu::fixture('foobar');
    fu::equal($foobar['foo'], 'bar', "the fixture 'foobar' should have a key 'foo' equal to 'baz'");

    $fooarr = array('blam'=>'blaz');
    fu::has('blam', $fooarr, "\$fooarr has a key named 'blam'");


    $fooobj = new \StdClass;
    $fooobj->blam = 'blaz';
    fu::has('blam', $fooobj, "\$fooobj has a property named 'blam'");
});

fu::run();
