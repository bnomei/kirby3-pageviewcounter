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
            'file' => function () {
                $old = realpath(kirby()->roots()->accounts() . '/../') . '/pageviewcounter.sqlite';
                if (!Dir::exists(kirby()->roots()->logs())) {
                    Dir::make(kirby()->roots()->logs());
                }
                $new = kirby()->roots()->logs() . '/pageviewcounter.sqlite';
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
            $url = $this->url(
                kirby()->languages()->count() > 1 ?
                    kirby()->languages()->first()->code() :
                    null
            );

            return \Kirby\Toolkit\Html::img(
                $url . '/counter-pixel',
                [
                    'loading' => 'lazy',
                    'alt' => 'pageview counter pixel',
                    'style' => option('bnomei.pageviewcounter.image.style'),
                ]
            );
        },
    ],
    'routes' => [
        [
            'pattern' => 'counter-pixel',
            'language' => '*',
            'action' => function ($language) {
                $hasUser = (option('bnomei.pageviewcounter.ignore-panel-users') && kirby()->user()) || intval(get('ignore', 0)) === 1;
                if ($hasUser === false) {
                    \Bnomei\PageViewCounter::singleton()->increment(
                        site()->homePage()->id(),
                        time()
                    );
                }
                \Bnomei\PageViewCounter::singleton()->pixel();
            },
        ],
        [
            'pattern' => '(:all)/counter-pixel',
            'language' => '*',
            'action' => function ($language, $id = null) {
                // single language setup
                if (!$id) {
                    $id = $language;
                }

                $hasUser = (option('bnomei.pageviewcounter.ignore-panel-users') && kirby()->user()) || intval(get('ignore', 0)) === 1;
                if ($hasUser === false) {
                    \Bnomei\PageViewCounter::singleton()->increment(
                        $id,
                        time()
                    );
                }
                \Bnomei\PageViewCounter::singleton()->pixel();
            },
        ],
    ],
]);
