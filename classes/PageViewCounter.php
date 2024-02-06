<?php

declare(strict_types=1);

namespace Bnomei;

use DeviceDetector\DeviceDetector;
use Exception;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Kirby\Toolkit\A;

final class PageViewCounter
{
    /** @var PageViewCountIncrementor $counter */
    private $counter;

    /** @var array $options */
    private $options;

    public function __construct(array $options = [])
    {
        $defaults = [
            'debug' => option('debug'),
            'counter' => \option('bnomei.pageviewcounter.counter'),
            'ignore-panel-users' => \option('bnomei.pageviewcounter.ignore-panel-users'),
            'CrawlerDetect' => option('bnomei.pageviewcounter.botDetection.CrawlerDetect'),
            'DeviceDetector' => option('bnomei.pageviewcounter.botDetection.DeviceDetector'),
        ];
        $this->options = array_merge($defaults, $options);

        $this->counter = $this->options['counter']();

        if ($this->option('debug')) {
            try {
                kirby()->cache('bnomei.pageviewcounter')->flush();
            } catch (Exception $e) {
                //
            }
        }
    }

    /**
     * @param string|null $key
     * @return array|mixed
     */
    public function option(?string $key = null)
    {
        if ($key) {
            return A::get($this->options, $key);
        }
        return $this->options;
    }

    public function increment(string $id, ?int $timestamp = null, int $count = 1): int
    {
        $timestamp = $timestamp ?? time();
        return $this->counter->increment($id, $timestamp, $count);
    }

    public function importAppend(array $data): int
    {
        $count = 0;
        foreach ($data as $item) {
            $this->increment($item['id'], $item['last_visited_at'], $item['viewcount']);
            $count++;
        }
        return $count;
    }

    public function count(string  $id): int
    {
        return $this->counter->count($id);
    }

    public function timestamp(string  $id): int
    {
        return $this->counter->timestamp($id);
    }

    public function pixel()
    {
        try {
            $IMG = \imagecreate(1, 1);
            $background = \imagecolorallocate($IMG, 0, 0, 0);
            \header("Content-type: image/png");
            \imagepng($IMG);
            \imagecolordeallocate($IMG, $background);
            \imagedestroy($IMG);
        } catch (Exception $e) {
            \header("Content-type: text/plain");
            echo $e->getMessage();
        }
        exit;
    }

    public function willTrack(): bool
    {
        if (intval(get('ignore', 0)) === 1) {
            return false;
        }

        $hasUser = $this->option('ignore-panel-users') && kirby()->user();
        if ($hasUser) {
            return false;
        }

        $useragent = A::get($_SERVER, "HTTP_USER_AGENT", '');

        if ($this->option('CrawlerDetect')) {
            $isCrawler = (new CrawlerDetect())->isCrawler($useragent);
            if ($isCrawler) {
                return false;
            }
        }

        if ($this->option('DeviceDetector')) {
            $device = new DeviceDetector($useragent);
            $device->discardBotInformation();
            $device->parse();
            if ($device->isBot()) {
                return false;
            }
        }

        return true;
    }

    /** @var PageViewCounter */
    private static $singleton;

    /**
     * @param array $options
     * @return PageViewCounter
     */
    public static function singleton(array $options = [])
    {
        if (!self::$singleton) {
            self::$singleton = new self($options);
        }

        return self::$singleton;
    }
}
