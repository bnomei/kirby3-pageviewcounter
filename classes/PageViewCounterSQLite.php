<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Database\Database;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Obj;

class PageViewCounterSQLite implements PageViewCountIncrementor
{
    /** @var \SQLite3 */
    private $database;

    /** @var array */
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'wal' => \option('bnomei.pageviewcounter.sqlite.wal'),
            'file' => \option('bnomei.pageviewcounter.sqlite.file'),
        ], $options);

        foreach ($this->options as $key => $call) {
            if (!is_string($call) && is_callable($call) && in_array($key, ['file'])) {
                $this->options[$key] = $call();
            }
        }

        $target = strval($this->options['file']);
        $this->database = new \SQLite3($target);
        if (\SQLite3::version() >= 3007001 && $this->options['wal']) {
            $this->database->exec("PRAGMA busy_timeout=1000");
            $this->database->exec("PRAGMA journal_mode = WAL");
        }
        $this->database->query("CREATE TABLE IF NOT EXISTS pageviewcount (id TEXT primary key unique, viewcount INTEGER, last_visited_at INTEGER)");
    }

    public function __destruct()
    {
        if (\SQLite3::version() >= 3007001 && $this->options['wal']) {
            $this->database()->exec('PRAGMA main.wal_checkpoint(TRUNCATE);');
        }
        $this->database()->close();
    }

    public function databaseFile(): string
    {
        return $this->options['file'];
    }

    public function database(): \SQLite3
    {
        return $this->database;
    }

    public function increment(string $id, int $timestamp, int $count = 1): ?int
    {
        $obj = $this->get($id);

        if ($obj === null) {
            $viewcount = $count;
            $this->database()->query("INSERT INTO pageviewcount (id, viewcount, last_visited_at) VALUES ('${id}', ${viewcount}, ${timestamp})");
        } else {
            $viewcount = intval($obj['viewcount']) + $count;
            $timestamp = intval($obj['last_visited_at']) < $timestamp ? $timestamp : $obj['last_visited_at'];
            $this->database()->query("UPDATE pageviewcount SET viewcount = ${viewcount}, last_visited_at = ${timestamp} WHERE id='${id}'");
        }

        return $viewcount;
    }

    public function get(string $id): ?array
    {
        $results = $this->database()
            ->query("SELECT * from pageviewcount WHERE id='${id}'")
            ->fetchArray(SQLITE3_ASSOC);

        return $results ? $results : null;
    }

    public function count(string $id): int
    {
        $obj = $this->get($id);
        return $obj ? intval($obj['viewcount']) : 0;
    }

    public function timestamp(string $id): int
    {
        $obj = $this->get($id);
        return $obj ? intval($obj['last_visited_at']) : 0;
    }
}
