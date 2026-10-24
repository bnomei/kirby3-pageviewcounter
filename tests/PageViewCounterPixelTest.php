<?php

test('pixel outputs only a valid one by one PNG without PHP diagnostics', function () {
    // pixel() exits, so exercise its public output in a separate PHP process.
    $process = proc_open([
        PHP_BINARY,
        '-d', 'error_reporting=-1',
        '-d', 'display_errors=stderr',
        '-d', 'log_errors=0',
        '-r', <<<'PHP'
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
require $argv[1];
// The pixel output does not depend on the counter or Kirby configuration.
$counter = (new ReflectionClass(Bnomei\PageViewCounter::class))->newInstanceWithoutConstructor();
$counter->pixel();
echo 'pixel did not exit';
PHP,
        __DIR__.'/../classes/PageViewCounter.php',
    ], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);

    expect($errors)->toBe('')
        ->and($status)->toBe(0);

    $image = imagecreatefromstring($output);
    expect(imagesx($image))->toBe(1)
        ->and(imagesy($image))->toBe(1);

    // Comparing the complete PNG also rejects diagnostics before or after it.
    $expected = imagecreate(1, 1);
    imagecolorallocate($expected, 0, 0, 0);
    ob_start();
    imagepng($expected);
    $png = ob_get_clean();

    expect($output)->toBe($png);
});
