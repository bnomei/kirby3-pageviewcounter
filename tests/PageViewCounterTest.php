<?php

require_once __DIR__.'/../vendor/autoload.php';

use Bnomei\PageViewCounter;
use Kirby\Toolkit\F;

test('construct', function () {
    $pvc = new PageViewCounter;
    expect($pvc)->toBeInstanceOf(\Bnomei\PageViewCounter::class);
});

test('creates db if missing', function () {
    $target = \option('bnomei.pageviewcounter.sqlite.file')();
    F::remove($target);
    new PageViewCounter([
        'counter' => function () {
            return new \Bnomei\PageViewCounterSQLite;
        },
    ]);
    expect($target)->toBeFile();
});

test('page view', function () {
    $id = site()->homePage()->id();
    $pvc = new PageViewCounter;
    $count = $pvc->count($id);
    $pvc->increment($id);
    $pvc->increment($id);
    $hit = $pvc->increment($id);
    expect($hit)->toEqual($count + 3);
});
