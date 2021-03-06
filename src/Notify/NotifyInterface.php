<?php declare(strict_types=1);

namespace Microparts\Errors\Notify;

use Throwable;

interface NotifyInterface
{
    /**
     * Simple interface to abstract from concrete notification driver.
     * This method designed for send notifications to stdout, sentry, bugsnag or anywhere.
     *
     * @param \Throwable $e
     * @param int $status
     *
     * @return void
     */
    public function notify(Throwable $e, int $status = 500): void;
}
