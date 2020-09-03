<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/pageviewcounter', [
    'options' => [
        'counter' => function () {
            //    return new \Bnomei\PageViewCounterField();
            return new \Bnomei\PageViewCounterSQLite();
        },
        'image' => [
            'style' => 'position: absolute; top: 100vh; right: 0; pointer-events: none; height: 1px; width: 1px; opacity: 0;',
        ],
        'field' => [
            'count' => 'viewcount',
            'timestamp' => 'lastvisited',
        ],
        'sqlite' => [
            'file' => function () {
                $dir = realpath(kirby()->roots()->accounts() . '/../');
                if (!Dir::exists($dir)) {
                    Dir::make($dir);
                }
                return $dir . '/pageviewcounter.sqlite';
            },
        ],
        'cache' => true,
    ],
    'pageMethods' => [
        'counterImage' => function () {
            return \Kirby\Toolkit\Html::img($this->url() . '/counter/' . time(), [
                'loading' => 'lazy',
                'alt' => 'pageview counter pixel',
                'style' => option('bnomei.pageviewcounter.image.style'),
            ]);
        },
    ],
    'routes' => [
        [
            'pattern' => 'counter/(:num)',
            'action' => function ($timestamp) {
                \Bnomei\PageViewCounter::singleton()->increment(
                    site()->homePage()->id(),
                    $timestamp
                );
                \Bnomei\PageViewCounter::singleton()->pixel();
            },
        ],
        [
            'pattern' => '(:all)/counter/(:num)',
            'action' => function ($id, $timestamp) {
                \Bnomei\PageViewCounter::singleton()->increment(
                    $id,
                    $timestamp
                );
                \Bnomei\PageViewCounter::singleton()->pixel();
            },
        ],
    ],
]);
