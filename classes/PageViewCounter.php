<?php

declare(strict_types=1);

namespace Bnomei;

use Closure;
use DeviceDetector\DeviceDetector;
use Exception;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Kirby\Toolkit\A;

use function option;

class PageViewCounter
{
    private PageViewCountIncrementor $counter;

    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'counter' => option('bnomei.pageviewcounter.counter'),
            'ignore-panel-users' => option('bnomei.pageviewcounter.ignore-panel-users'),
            'CrawlerDetect' => option('bnomei.pageviewcounter.botDetection.CrawlerDetect'),
            'DeviceDetector' => option('bnomei.pageviewcounter.botDetection.DeviceDetector'),
        ], $options);

        $this->counter = $this->options['counter'] instanceof Closure ? $this->options['counter']() : $this->options['counter'];
    }

    public function option(?string $key = null): mixed
    {
        if ($key) {
            return A::get($this->options, $key);
        }

        return $this->options;
    }

    public function increment(string $id, ?int $timestamp = null, int $count = 1): int
    {
        $timestamp = $timestamp ?? time();

        return $this->counter->increment($id, $timestamp, $count) ?? 0;
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

    public function count(string $id): int
    {
        return $this->counter->count($id);
    }

    public function timestamp(string $id): int
    {
        return $this->counter->timestamp($id);
    }

    public function pixel(): void
    {
        try {
            $IMG = \imagecreate(1, 1);
            $background = \imagecolorallocate($IMG, 0, 0, 0);
            \header('Content-type: image/png');
            \imagepng($IMG);
            \imagecolordeallocate($IMG, is_int($background) ? $background : 0);
            \imagedestroy($IMG);
        } catch (Exception $e) {
            \header('Content-type: text/plain');
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

        $useragent = A::get($_SERVER, 'HTTP_USER_AGENT', '');

        if ($this->option('CrawlerDetect')) {
            $isCrawler = (new CrawlerDetect)->isCrawler($useragent);
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

    private static ?self $singleton = null;

    public static function singleton(array $options = []): self
    {
        if (self::$singleton === null) {
            self::$singleton = new self($options);
        }

        return self::$singleton;
    }
}
