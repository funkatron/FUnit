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

fu::test('Checking for exceptions', function() {
    $callback = function() { throw new RuntimeException(); };
    fu::throws($callback, 'RuntimeException', 'Correct exception');

    $callback = function($foo) { throw new RuntimeException($foo); };
    fu::throws($callback, array('bar'), 'LogicException', 'Not the correct exception');
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
