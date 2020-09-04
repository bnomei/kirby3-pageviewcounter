# Kirby 3 Pageview Counter

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-pageviewcounter?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-pageviewcounter?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-pageviewcounter)](https://travis-ci.com/bnomei/kirby3-pageviewcounter)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-pageviewcounter)](https://coveralls.io/github/bnomei/kirby3-pageviewcounter) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-pageviewcounter)](https://codeclimate.com/github/bnomei/kirby3-pageviewcounter) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Track Page view count and last visited timestamp

## Usage

Echo the tracking 1x1px image anywhere in your template.

```php
<?php echo $page->counterImage(); ?>
```

## How it works

The tracking image will be moved below the fold and trigger the counter [with native lazy loading](https://caniuse.com/#feat=loading-lazy-attr) on first user scroll. Why? To avoid most of the bots.

## SQLite database (default)

**site/config/config.php**
```php
<?php
return [
    /* default 
    'bnomei.pageviewcounter.counter' => function () {
            return new \Bnomei\PageViewCounterSQLite();
    },
    'bnomei.pageviewcounter.sqlite.wal' => true, // sqlite WAL for faster IO
    'bnomei.pageviewcounter.sqlite.file' => function () {
        $dir = realpath(kirby()->roots()->accounts() . '/../');
        Dir::make($dir);
        return $dir . '/pageviewcounter.sqlite';
    },
    */
    // other options ...
];
```

## Page Fields (alternative)

**site/config/config.php**
```php
<?php
return [ 
    'bnomei.pageviewcounter.field.count' => 'viewcount',
    'bnomei.pageviewcounter.field.timestamp' => 'lastvisited',
    'bnomei.pageviewcounter.counter' => function () {
            return new \Bnomei\PageViewCounterField();
    },
    // other options ...
];
```

> NOTE: Be warned that Page Fields might not work well for concurrent requests.

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-pageviewcounter/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
