<?php

require_once __DIR__.'/../vendor/autoload.php';

use Bnomei\PageViewCounter;
use Bnomei\PageViewCounterSQLite;
use Kirby\Toolkit\F;

test('construct', function () {
    $pvc = new PageViewCounter;
    expect($pvc)->toBeInstanceOf(PageViewCounter::class);
});

test('creates db if missing', function () {
    $target = \option('bnomei.pageviewcounter.sqlite.file')();
    F::remove($target);
    new PageViewCounter([
        'counter' => function () {
            return new PageViewCounterSQLite;
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

test('sqlite counter treats ids with quotes as literal values', function () {
    $target = sys_get_temp_dir().'/pageviewcounter-test-'.uniqid().'.sqlite';
    F::remove($target);

    $counter = new PageViewCounterSQLite([
        'file' => $target,
    ]);

    $counter->increment('safe', 100);
    $counter->increment("safe' OR 1=1 --", 200);

    expect($counter->count('safe'))->toEqual(1)
        ->and($counter->timestamp('safe'))->toEqual(100)
        ->and($counter->count("safe' OR 1=1 --"))->toEqual(1)
        ->and($counter->timestamp("safe' OR 1=1 --"))->toEqual(200);

    F::remove($target);
});
