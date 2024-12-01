<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Page;
use Kirby\Content\Field;

use function option;

class PageViewCounterField implements PageViewCountIncrementor
{
    private string $fieldcount;

    private string $fieldtimestamp;

    public function __construct()
    {
        $this->fieldcount = strval(option('bnomei.pageviewcounter.field.count'));
        $this->fieldtimestamp = strval(option('bnomei.pageviewcounter.field.timestamp'));
    }

    private function pageFromID(?string $id = null): ?Page
    {
        return function_exists('bolt') ? \bolt($id) : \page($id);
    }

    public function increment(string $id, int $timestamp, int $count = 1): ?int
    {
        $page = $this->pageFromID($id);
        if (! $page) {
            return null;
        }

        $current = $this->get($id);
        $current[$this->fieldcount] += $count;
        $current[$this->fieldtimestamp] = $current[$this->fieldtimestamp] < $timestamp ? $timestamp : $current[$this->fieldtimestamp];

        $page = kirby()->impersonate('kirby', function () use ($page, $current) {
            return $page->update($current);
        });

        return $current[$this->fieldcount];
    }

    public function get(string $id): array
    {
        $page = $this->pageFromID($id);
        /** @var Field $field */
        $field = $page->{$this->fieldcount}();
        if ($field->isNotEmpty()) {
            return [
                $this->fieldcount => $field->toInt(), // @phpstan-ignore-line
                $this->fieldtimestamp => $page->{$this->fieldtimestamp}()->toInt(),
            ];
        }

        return [
            $this->fieldcount => 0,
            $this->fieldtimestamp => 0,
        ];
    }

    public function count(string $id): int
    {
        return $this->get($id)[$this->fieldcount];
    }

    public function timestamp(string $id): int
    {
        return $this->get($id)[$this->fieldtimestamp];
    }
}
