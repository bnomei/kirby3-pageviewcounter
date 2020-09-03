<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Bnomei\PageViewCounter;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

final class PageViewCounterTest extends TestCase
{
    public function testConstruct()
    {
        $pvc = new PageViewCounter();
        $this->assertInstanceOf(\Bnomei\PageViewCounter::class, $pvc);
    }

    public function testCreatesDBIfMissing()
    {
        $target = \option('bnomei.pageviewcounter.sqlite.file')();
        F::remove($target);
        new PageViewCounter();
        $this->assertFileExists($target);
    }

    public function testPageView()
    {
        $id = site()->homePage()->id();
        $pvc = new PageViewCounter();
        $count = $pvc->count($id);
        $pvc->increment($id);
        $pvc->increment($id);
        $hit = $pvc->increment($id);
        $this->assertEquals($count + 3, $hit);
    }
}
