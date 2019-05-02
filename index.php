<?php

use Microparts\Errors\Error;
use Microparts\Errors\Notify\LoggerNotify;
use Microparts\Logger\Logger;
use Psr\Log\LogLevel;

require __DIR__ . '/vendor/autoload.php';

$error = Error::new($debug = true);
$error->addNotifier(new LoggerNotify(Logger::new('Error', LogLevel::DEBUG)));

try {
    throw new PDOException('test');
} catch (Throwable $e) {
    $response = $error->capture($e);
    dd($response->getBody()->getContents());
}

