<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/pageviewcounter', [
    'options' => [
        'ignore-panel-users' => true,
        'counter' => function () {
            // return new \Bnomei\PageViewCounterField();
            return new \Bnomei\PageViewCounterSQLite();
        },
        'image' => [
            'style' => 'position: absolute; top: 100vh; left: 0; pointer-events: none; height: 1px; width: 1px; opacity: 0;',
        ],
        'field' => [
            'count' => 'viewcount',
            'timestamp' => 'lastvisited',
        ],
        'sqlite' => [
            'wal' => true,
            'file' => function () {
                $old = realpath(kirby()->roots()->accounts() . '/../') . '/pageviewcounter.sqlite';
                $new = realpath(kirby()->roots()->log()) . '/pageviewcounter.sqlite';
                // migrate
                if (F::exists($old)) {
                    F::move($old, $new, true);
                }
                return $new;
            },
        ],
        'cache' => true,
    ],
    'fields' => [
        'viewcount' => [
            'computed' => [
                'value' => function () {
                    return \Bnomei\PageViewCounter::singleton()->count($this->model()->id());
                },
            ],
        ],
        'lastvisited' => [
            'props' => [
                'format' => function (?string $format = null) {
                    return $format ?? 'YYYY-MM-DD HH:m:s';
                },
            ],
            'computed' => [
                'value' => function () {
                    return date('c', \Bnomei\PageViewCounter::singleton()->timestamp($this->model()->id()));
                },
            ],
        ],
    ],
    'pageMethods' => [
        'counterImage' => function () {
            $user = (kirby()->user() && option('bnomei.pageviewcounter.ignore-panel-users')) ||
                intval(get('ignore', 0)) === 1 ?
                'ignore' :
                'visitor';
            return \Kirby\Toolkit\Html::img($this->url(
                kirby()->languages()->count() > 1 ?
                        kirby()->languages()->first()->code() :
                        null
            ) . '/counter/' . time() . '/' . $user, [
                'loading' => 'lazy',
                'alt' => 'pageview counter pixel',
                'style' => option('bnomei.pageviewcounter.image.style'),
            ]);
        },
    ],
    'routes' => [
        [
            'pattern' => 'counter/(:num)/(:any)',
            'language' => '*',
            'action' => function ($language, $timestamp, $action = null) {
                // single language setup
                if (!$action) {
                    $action = $timestamp;
                    $timestamp = $language;
                }
                if ($action === 'visitor') {
                    \Bnomei\PageViewCounter::singleton()->increment(
                        site()->homePage()->id(),
                        $timestamp
                    );
                }
                \Bnomei\PageViewCounter::singleton()->pixel();
            },
        ],
        [
            'pattern' => '(:all)/counter/(:num)/(:any)',
            'language' => '*',
            'action' => function ($language, $id, $timestamp, $action = null) {
                // single language setup
                if (!$action) {
                    $action = $timestamp;
                    $timestamp = $id;
                    $id = $language;
                }
                if ($action === 'visitor') {
                    \Bnomei\PageViewCounter::singleton()->increment(
                        $id,
                        $timestamp
                    );
                }
                \Bnomei\PageViewCounter::singleton()->pixel();
            },
        ],
    ],
]);
