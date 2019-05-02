<?php declare(strict_types=1);

namespace Microparts\Errors\Notify;

use SplQueue;
use Throwable;

final class NotifyPool implements NotifyInterface
{
    /**
     * @var \SplQueue
     */
    private $pool;

    /**
     * NotifyPool constructor.
     */
    public function __construct()
    {
        $this->pool = new SplQueue();
    }

    /**
     * Subscribes to newsletter about caused errors.
     *
     * @param \Microparts\Errors\Notify\NotifyInterface $notify
     *
     * @return \Microparts\Errors\Notify\NotifyPool
     */
    public function subscribe(NotifyInterface $notify): self
    {
        $this->pool->push($notify);

        return $this;
    }

    /**
     * Notify all subscribers about caused errors.
     *
     * @param \Throwable $e
     * @param int $status
     *
     * @return void
     */
    public function notify(Throwable $e, int $status = 500): void
    {
        while (!$this->pool->isEmpty()) {
            /** @var \Microparts\Errors\Notify\NotifyInterface $notifier */
            $notifier = $this->pool->dequeue();
            $notifier->notify($e, $status);
        }
    }
}
