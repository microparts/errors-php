Errors PHP package
==================

[![CircleCI](https://circleci.com/gh/microparts/errors-php.svg?style=svg)](https://circleci.com/gh/microparts/errors-php)
[![codecov](https://codecov.io/gh/microparts/errors-php/branch/master/graph/badge.svg)](https://codecov.io/gh/microparts/errors-php)

Is a complex solution to handling exceptions for any web application.
Compatible with [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) (logger) 
and [PSR-7](http://www.php-fig.org/psr/psr-7/) (http messages) recommendations.  

Key features:
* Framework agnostic (can be integrated with any php web app);
* You can define custom validation and error format for http response;
* You can define custom error handler;
* You can send error messages (debug output) to any service (sentry, bugsnag), classic logger or use both together;
* You can replace sensitivity data from exception message (like sql-queries in PDOException);
* You can mark exception like "silent" and it will be don't report to notification channels;
* 100% tests coverage :) 

## Install

As usual, install it through composer:

```bash
composer require microparts/errors-php
```

## Usage

Library does not handle errors globally. So that it works for you it needs 
a configure in application service provider.

Example how to does it:

```php
// Simple example for most cases
use Microparts\Errors\Error;

$error = Error::new($debug = true); // debug parameter add exception trace to http error response.
```
    
By default will be registered `PDOException` as "masked" and it exception message will be replaced with "Database error occurred. See logs for details.".
Also default `ValidationException` will be registered as "silent" for skip notifications about user mistakes. 

Second step is a exception capture in place where your app handle errors:

```php
use Microparts\Errors\Error;

$error = Error::new($debug = true);

try {
    throw new PDOException('test');
} catch (Throwable $e) {
    return $error->capture($e); // return PSR ResponseInterface
}
```

Method `capture` returns a PSR ResponseInterface with formatted response to provide it to user.

## Hard usage

Hard in quotes of course :)

`Error` object may be configured what you like. Example:

```php
// Simple example for most cases
use Microparts\Errors\Error;
use Microparts\Errors\Notify\LoggerNotify;
use Microparts\Errors\Notify\SentryNotify;
use Microparts\Logger\Logger;
use Microparts\Errors\Formatter\DefaultErrorFormatter;
use Microparts\Errors\Handler\DefaultErrorHandler;

$error = new Error($debug = false);
$error->setFormatter(new DefaultErrorFormatter($debug));
$error->setHandler(new DefaultErrorHandler());
$error->addSilentException(LogicException::class); // logic is not a need thing 
$error->addDatabaseException(RedisException::class);
// Logger PSR compatible.
// If you want use my pretty logger, just run command: 
// composer require microparts/logs-php
$error->addNotifier(new LoggerNotify(Logger::new()));
$error->addNotifier(new SentryNotify(['dsn' => $dsn]));
```

That all. Use with ❤.

## Sentry & Notify both channels

Sentry integration available. Install sentry sdk through composer: 

```bash
composer require sentry/sdk
```

...and then register new notifier:
```php
use Microparts\Errors\Error;
use Microparts\Errors\Notify\LoggerNotify;
use Microparts\Errors\Notify\SentryNotify;
use Microparts\Logger\Logger;

$error = Error::new();
$error->addNotifier(new LoggerNotify(Logger::new()));
$error->addNotifier(new SentryNotify(['dsn' => $dsn]));
````

Two notifies will be works together and send notifications to both channels.
Now you should be capture exceptions in the try/catch block.

## Tests

* 100% tests coverage
* `vendor/bin/phpunit`


## License

The MIT License

Copyright © 2019 teamc.io, Inc. https://teamc.io

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

