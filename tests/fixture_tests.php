<?php

use \FUnit as fu;

require_once __DIR__ . '/../src/FUnit.php';

fu::suite('Fixture test suite');

fu::test('Test adding fixtures', function () {
    fu::fixture('a', array(1,2,3));
    $a = fu::fixture('a');
    fu::strict_equal(array(1,2,3), $a);
});

fu::test('Test resetting fixtures', function () {
    fu::fixture('a', array(1,2,3));
    $a = fu::fixture('a');
    fu::reset_fixtures();
    fu::ok(is_null(fu::fixture('a')));
});


fu::run();
