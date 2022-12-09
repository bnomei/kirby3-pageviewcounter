<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Database\Database;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;

class PageViewCounterSQLite implements PageViewCountIncrementor
{
    /** @var Database */
    private $database;

    /** @var array */
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'file' => \option('bnomei.pageviewcounter.sqlite.file'),
        ], $options);

        foreach ($this->options as $key => $call) {
            if (!is_string($call) && is_callable($call) && in_array($key, ['file'])) {
                $this->options[$key] = $call();
            }
        }

        $target = $this->options['file'];
        if (!F::exists($target)) {
            $db = new \SQLite3($target);
            $db->exec("CREATE TABLE IF NOT EXISTS pageviewcount (id TEXT primary key unique, viewcount INTEGER, last_visited_at INTEGER)");
            $db->close();
        }

        $this->database = new Database([
            'type' => 'sqlite',
            'database' => $target,
        ]);
    }

    public function databaseFile(): string
    {
        return $this->options['file'];
    }

    public function database(): Database
    {
        return $this->database;
    }

    public function increment(string $id, int $timestamp, int $count = 1): ?int
    {
        $obj = $this->get($id);

        if ($obj === null) {
            $viewcount = $count;
            $this->database()->query("INSERT INTO pageviewcount (id, viewcount, last_visited_at) VALUES ('$id', $viewcount, $timestamp)");
        } else {
            $viewcount = $obj->viewcount + $count;
            $timestamp = $obj->last_visited_at < $timestamp ? $timestamp : $obj->last_visited_at;
            $this->database()->query("UPDATE pageviewcount SET viewcount = $viewcount, last_visited_at = $timestamp WHERE id='$id'");
        }

        return $viewcount;
    }

    public function get(string $id): ?Obj
    {
        foreach ($this->database()->query("SELECT * from pageviewcount WHERE id='$id'") as $obj) {
            return $obj;
        }
        return null;
    }

    public function count(string $id): int
    {
        $obj = $this->get($id);
        return $obj ? intval($obj->viewcount) : 0;
    }

    public function timestamp(string $id): int
    {
        $obj = $this->get($id);
        return $obj ? intval($obj->last_visited_at) : 0;
    }
}
