<?php declare(strict_types=1);

namespace Microparts\Errors\Notify;

use Psr\Log\LoggerInterface;
use Throwable;

final class LoggerNotify extends AbstractNotify
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * LoggerNotify constructor.
     *
     * Integration with any logger who supports PSR LoggerInterface.
     * Monolog of course.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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
        $this->logger->error(sprintf('[%d] %s', $status, $e->getMessage()), $this->toArray($e));
    }
}
