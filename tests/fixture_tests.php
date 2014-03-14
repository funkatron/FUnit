<?php

use \FUnit as fu;
use \FUnit\TestSuite;

require_once __DIR__ . '/../src/FUnit.php';

fu::suite('Fixture test suite');

fu::test('Test adding fixtures', function () {

    fu::fixture('a', array(1,2,3));
    $a = fu::fixture('a');
    fu::strict_equal(array(1,2,3), $a);
});

fu::run();
