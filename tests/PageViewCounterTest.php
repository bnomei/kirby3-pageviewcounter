<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Bnomei\PageViewCounter;
use PHPUnit\Framework\TestCase;

final class PageViewCounterTest extends TestCase
{
    public function testConstruct()
    {
        $pvc = PageViewCounter::singleton();
        $this->assertInstanceOf(\Bnomei\PageViewCounter::class, $pvc);
    }
}
