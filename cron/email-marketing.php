<?php

declare(strict_types=1);

use App\Support\MarketingQueue;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';

try {
    $result = MarketingQueue::processNext();
    fwrite(STDOUT, '[' . date('Y-m-d H:i:s') . '] ' . $result['message'] . PHP_EOL);
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] ERROR: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
