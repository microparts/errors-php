<?php declare(strict_types=1);

namespace Microparts\Errors\Handler;

use Microparts\Errors\Formatter\FormatterInterface;
use Microparts\Errors\Notify\NotifyPool;
use Psr\Http\Message\ResponseInterface;
use SplQueue;
use Throwable;

interface HandlerInterface
{
    /**
     * Handles the exceptions based on it types and preferences.
     *
     * @param \Throwable $e
     * @param \SplQueue $queue
     * @param \Microparts\Errors\Formatter\FormatterInterface $formatter
     * @param \Microparts\Errors\Notify\NotifyPool $pool
     *
     * @return ResponseInterface
     */
    public function handle(Throwable $e, SplQueue $queue, FormatterInterface $formatter, NotifyPool $pool): ResponseInterface;
}
