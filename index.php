<?php

use Microparts\Errors\Error;
use Microparts\Errors\Notify\LoggerNotify;
use Microparts\Errors\Notify\SentryNotify;
use Microparts\Logger\Logger;
use Psr\Log\LogLevel;

require __DIR__ . '/vendor/autoload.php';

$error = Error::new($debug = true);
$error->addNotifier(new LoggerNotify(Logger::new('Error', LogLevel::DEBUG)));
$error->addNotifier(new SentryNotify(['dsn' => 'https://48550b9873eb4f3dbf86853f178fba54@sentry.io/1451506']));

try {
    throw new PDOException('test');
} catch (Throwable $e) {
    $response = $error->capture($e);
    dd($response->getBody()->getContents());
}

