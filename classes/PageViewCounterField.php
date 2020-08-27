<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Page;
use Kirby\Toolkit\F;

class PageViewCounterField implements PageViewCountIncrementor
{
    /** @var string $fieldcount */
    private $fieldcount;

    /** @var string $fieldtimestamp */
    private $fieldtimestamp;

    public function __construct()
    {
        $this->fieldcount = \option('bnomei.pageviewcounter.field.count');
        $this->fieldtimestamp = \option('bnomei.pageviewcounter.field.timestamp');
    }

    private function pageFromID($id): ?Page
    {
        return function_exists('bolt') ? \bolt($id) : \page($id);
    }

    public function increment(string $id, int $timestamp,  int $count = 1): ?int
    {
        $page = $this->pageFromID($id);
        if (!$page) {
            return null;
        }

        $current = $this->get($id);
        $current[$this->fieldcount] += $count;
        $current[$this->fieldtimestamp] = $current[$this->fieldtimestamp] < $timestamp ? $timestamp : $current[$this->fieldtimestamp];

        kirby()->impersonate('kirby');
        $this->page = $page->update($current);

        return $current[$this->fieldcount];
    }

    public function get(string $id): array
    {
        $page = $this->pageFromID($id);
        $field = $page->{$this->fieldcount}();
        if ($field->isNotEmpty()) {
            return [
                $this->fieldcount => $field->toInt(),
                $this->fieldtimestamp => $page->{$this->fieldtimestamp}()->toInt(),
            ];
        }
        return [
            $this->fieldcount => 0,
            $this->fieldtimestamp => 0,
        ];
    }
}
