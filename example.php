<?php
use \FUnit\fu;

require __DIR__ . '/FUnit.php';

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

fu::test('Forced failure', function() {
	fu::fail('This is a forced fail');
});


fu::test('Expected failure', function() {
	fu::expect_fail('This is a good place to describe a missing test');
});


fu::test('Forced Errors/Exception', function() {

	trigger_error('This was triggered inside a test', E_USER_ERROR);

	trigger_error('This was triggered inside a test', E_USER_NOTICE);

	throw new Exception('This was thrown inside a test');
});


fu::run();

// this should output an empty array, because our fixtures will be gone
var_dump(fu::$fixtures);