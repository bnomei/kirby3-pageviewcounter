<?php

@include_once __DIR__.'/vendor/autoload.php';

Kirby::plugin('bnomei/pageviewcounter', [
    'options' => [
        'ignore-panel-users' => true,
        'counter' => function () {
            // return new \Bnomei\PageViewCounterField();
            return new \Bnomei\PageViewCounterSQLite;
        },
        'image' => [
            'style' => 'position: absolute; top: 100vh; left: 0; pointer-events: none; height: 1px; width: 1px; opacity: 0;',
        ],
        'field' => [
            'count' => 'viewcount',
            'timestamp' => 'lastvisited',
        ],
        'botDetection' => [
            'CrawlerDetect' => true, // almost no overhead, ~10ms
            'DeviceDetector' => true, // ~40ms
        ],
        'sqlite' => [
            'file' => function () {
                $old = realpath(kirby()->roots()->accounts().'/../').'/pageviewcounter.sqlite';
                if (! Dir::exists(kirby()->roots()->logs())) {
                    Dir::make(kirby()->roots()->logs());
                }
                $new = kirby()->roots()->logs().'/pageviewcounter.sqlite';
                // migrate
                if (F::exists($old)) {
                    F::move($old, $new, true);
                }

                return $new;
            },
        ],
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
    'pagesMethods' => [
        'pageviewcount' => function (int $base = 0): int {
            $count = $base;
            foreach ($this as $page) {
                /** @var \Kirby\Cms\Page $page */
                $count += $page->pageviewcount();
                if ($page->hasChildren()) {
                    $count += $page->children()->pageviewcount();
                }
            }

            return $count;
        },
    ],
    'pageMethods' => [
        'pageviewcount' => function (): int {
            return \Bnomei\PageViewCounter::singleton()->count($this->id()); // id NOT uuid!
        },
        'pagelastvisited' => function ($format = 'c'): string {
            return date($format, \Bnomei\PageViewCounter::singleton()->timestamp($this->id())); // id NOT uuid!
        },
        'counterImage' => function () {
            $url = $this->url(
                kirby()->languages()->count() > 1 ?
                    kirby()->languages()->first()->code() :
                    null
            );

            return \Kirby\Toolkit\Html::img(
                $url.'/counter-pixel',
                [
                    'loading' => 'lazy',
                    'alt' => 'pageview counter pixel',
                    'style' => option('bnomei.pageviewcounter.image.style'),
                ]
            );
        },
        'counterCss' => function () {
            $url = $this->url(
                kirby()->languages()->count() > 1 ?
                    kirby()->languages()->first()->code() :
                    null
            );

            return '<style>body:hover{border-width:0;border-image: url("'.$url.'/counter-pixel")}</style>';
        },
    ],
    'routes' => [
        [
            'pattern' => 'counter-pixel',
            'language' => '*',
            'action' => function ($language = null) {
                $pvc = \Bnomei\PageViewCounter::singleton();
                if ($pvc->willTrack()) {
                    $pvc->increment(
                        site()->homePage()?->id() ?? 'home',
                        time()
                    );
                }
                $pvc->pixel();
            },
        ],
        [
            'pattern' => '(:all)/counter-pixel',
            'language' => '*',
            'action' => function ($language, $id = null) {
                // single language setup
                if (! $id) {
                    $id = $language;
                }

                $pvc = \Bnomei\PageViewCounter::singleton();
                if ($pvc->willTrack()) {
                    $pvc->increment(
                        $id,
                        time()
                    );
                }
                $pvc->pixel();
            },
        ],
    ],
]);
