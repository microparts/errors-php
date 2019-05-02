<?php declare(strict_types=1);

namespace Microparts\Errors\Notify;

use Throwable;

final class NullNotify extends AbstractNotify
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
    public function notify(Throwable $e, int $status = 500): void
    {
        // send info to black hole, like this https://www.jpl.nasa.gov/images/universe/20190410/blackhole20190410.jpg
    }
}
