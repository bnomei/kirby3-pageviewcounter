<?php

declare(strict_types=1);

namespace Bnomei;

interface PageViewCountIncrementor
{
    public function increment(string $id, int $timestamp, int $count = 1): ?int;
    public function get(string $id);
    public function count(string $id): int;
    public function timestamp(string $id): int;
}
