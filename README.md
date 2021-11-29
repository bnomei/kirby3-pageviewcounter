# Kirby 3 Pageview Counter

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-pageviewcounter?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-pageviewcounter?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-pageviewcounter)](https://travis-ci.com/bnomei/kirby3-pageviewcounter)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-pageviewcounter)](https://coveralls.io/github/bnomei/kirby3-pageviewcounter) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-pageviewcounter)](https://codeclimate.com/github/bnomei/kirby3-pageviewcounter) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Track Page view count and last visited timestamp

## Commercial Usage

> <br>
> <b>Support open source!</b><br><br>
> This plugin is free but if you use it in a commercial project please consider to sponsor me or make a donation.<br>
> If my work helped you to make some cash it seems fair to me that I might get a little reward as well, right?<br><br>
> Be kind. Share a little. Thanks.<br><br>
> &dash; Bruno<br>
> &nbsp; 

| M | O | N | E | Y |
|---|----|---|---|---|
| [Github sponsor](https://github.com/sponsors/bnomei) | [Patreon](https://patreon.com/bnomei) | [Buy Me a Coffee](https://buymeacoff.ee/bnomei) | [Paypal dontation](https://www.paypal.me/bnomei/15) | [Buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170) |

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-pageviewcounter/archive/master.zip) as folder `site/plugins/kirby3-pageviewcounter` or
- `git submodule add https://github.com/bnomei/kirby3-pageviewcounter.git site/plugins/kirby3-pageviewcounter` or
- `composer require bnomei/kirby3-pageviewcounter`

## Usage

Echo the tracking 1x1px image anywhere in your template.

```php
<?php echo $page->counterImage(); ?>
```

## How it works

The tracking image will be moved below the fold and trigger the counter [with native lazy loading](https://caniuse.com/#feat=loading-lazy-attr) on first user scroll. Why? To avoid most of the bots.

## SQLite database (default)

To view the tracked count and timestamp this plugin provides two optional fields.

**in your page blueprint**
```yml
fields:
  counter:
    label: Page view count
    type: viewcount
  lastvisited:
    label: Page last visited
    type: lastvisited
    # format: 'DD-MM-YYYY'
```

> Kirby has *day.js* built in which you can use to [format your date](https://day.js.org/docs/en/display/format) output.

You do not have to add anything to you config files. But you could make some changes to the defaults, like the path to the sqlite file if you wanted to.

**site/config/config.php**
```php
<?php
return [
    /* default 
    'bnomei.pageviewcounter.counter' => function () {
        return new \Bnomei\PageViewCounterSQLite();
    },
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

If you do not want to store your tracked counts in a sqlite file then add kirby text or hidden fields to your blueprint. I usually have blueprints for them and extend them in my target pages blueprints. like this...

**site/blueprints/fields/viewcount.yml**
```yml
type: number
min: 0
default: 0
disabled: true
label: Visit Count
```

**site/blueprints/fields/lastvisited.yml**
```yml
type: hidden
```

**in your page blueprint**
```yml
fields:
  viewcount:
    extends: fields/viewcount
  lastvisited:
    extends: fields/lastvisited
```

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

## Settings

| bnomei.pageviewcounter.            | Default        | Description               |            
|---------------------------|----------------|---------------------------|
| field.counter | `fn()` | callback. returns instance of sqlite or field counter class |
| field.ignore-panel-users | `true` | boolean. if `true` will not increment count if session is by a panel user. |
| field.sqlite.file | `fn()` | callback. returns filepath to sqlite file |
| field.field.count | `viewcount` | string. name of field in page blueprint |
| field.field.timestamp | `lastvisited` | string. name of field in page blueprint |

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-pageviewcounter/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
