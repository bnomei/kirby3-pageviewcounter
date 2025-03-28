# Kirby Pageview Counter

[![Kirby 5](https://flat.badgen.net/badge/Kirby/5?color=ECC748)](https://getkirby.com)
![PHP 8.2](https://flat.badgen.net/badge/PHP/8.2?color=4E5B93&icon=php&label)
![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-pageviewcounter?color=ae81ff&icon=github&label)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-pageviewcounter?color=272822&icon=github&label)
[![Coverage](https://flat.badgen.net/codeclimate/coverage/bnomei/kirby3-pageviewcounter?icon=codeclimate&label)](https://codeclimate.com/github/bnomei/kirby3-pageviewcounter)
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-pageviewcounter?icon=codeclimate&label)](https://codeclimate.com/github/bnomei/kirby3-pageviewcounter/issues)
[![Discord](https://flat.badgen.net/badge/discord/bnomei?color=7289da&icon=discord&label)](https://discordapp.com/users/bnomei)
[![Buymecoffee](https://flat.badgen.net/badge/icon/donate?icon=buymeacoffee&color=FF813F&label)](https://www.buymeacoffee.com/bnomei)

Track Page view count and last visited timestamp

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-pageviewcounter/archive/master.zip) as folder `site/plugins/kirby3-pageviewcounter` or
- `git submodule add https://github.com/bnomei/kirby3-pageviewcounter.git site/plugins/kirby3-pageviewcounter` or
- `composer require bnomei/kirby3-pageviewcounter`

## Usage

Echo the tracking a `1px x 1px`-image or adding css anywhere in your template. Both techniques will increment the counter but technically they track in a different way.

**A) via image, tracking scroll below first fold**
```php
<?php echo $page->counterImage(); ?>
```

**B) via css, tracking mouse hover on body**
```php
<?php echo $page->counterCss(); ?>
```

## How it works

The tracking image will be moved below the fold and trigger the counter [with native lazy loading](https://caniuse.com/#feat=loading-lazy-attr) on first user scroll. Why? To avoid most of the bots. It will also work for cached pages using the [pages cache](https://getkirby.com/docs/reference/system/options/cache) (even when cached [static](https://github.com/getkirby/staticache)).

## SQLite database (default)

To view the tracked count and timestamp this plugin provides two optional fields (`viewcount` and `lastvisited`).

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

> [!TIP]
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

> [!WARNING]
> Be warned that Page Fields might not work well for concurrent requests.

## Usage in Stats section via page-methods

Thanks to an PR by @TinQ0 it's also possible to use the tracked count and timestamp in sections like Pages or Stats.

**in your page blueprint**
```yml
sections:
  stats:
    type: stats
    reports:
     - label: View count of page
       value: "{{ page.pageviewcount }}"
     - label: View count of children + count of page itself
       value: "{{ page.children.pageviewcount(page.pageviewcount) }}"
     - label: Last visited
       value: "{{ page.pagelastvisited }}" # page.pagelastvisited('Y-m-d H:i:s')
  pages:
    type: pages
    info: "{{ page.pageviewcount }}" # show view count of each page
```

> [!NOTE]
> The `pageviewcount`/`pagelastvisited` methods on the page object are prefix with `page*` to avoid clashing with the plain fields `viewcount`/`lastvisited` from the *page field*-setup variation.

## Settings

| bnomei.pageviewcounter.            | Default        | Description               |            
|---------------------------|----------------|---------------------------|
| field.counter | `fn()` | callback. returns instance of sqlite or field counter class |
| field.ignore-panel-users | `true` | boolean. if `true` will not increment count if session is by a panel user. |
| field.sqlite.file | `fn()` | callback. returns filepath to sqlite file |
| field.field.count | `viewcount` | string. name of field in page blueprint |
| field.field.timestamp | `lastvisited` | string. name of field in page blueprint |
| botDetection.CrawlerDetect   | `true`  | check for crawlers (~10ms)                                                        |
| botDetection.DeviceDetector   | `true`  | check for bots (~40ms)                                                                     |

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-pageviewcounter/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
