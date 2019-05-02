<?php declare(strict_types=1);

namespace Microparts\Errors\Tests\Notify;

use Exception;
use Microparts\Errors\Notify\LoggerNotify;
use Microparts\Errors\Notify\NotifyInterface;
use Microparts\Errors\Notify\NotifyPool;
use Microparts\Errors\Notify\NullNotify;
use Microparts\Errors\Notify\SentryNotify;
use Microparts\Errors\Tests\TestCase;
use Psr\Log\LoggerInterface;
use Throwable;

class NotifyTest extends TestCase
{
    public function testHowClassCreateNewInstanceWithoutArgs()
    {
        try {
            new NotifyPool();
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            printf($e);
            $this->assertTrue(false);
        }
    }

    public function testLoggerIntegration()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->willReturn(null);

        $notifier = new LoggerNotify($logger);
        $notifier->notify(new Exception(), 704);
    }

    public function testNullLogger()
    {
        try {
            $notifier = new NullNotify();
            $notifier->notify(new Exception(), 704);
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->assertTrue(false);
        }
    }

    public function testHowToPoolSendMessages()
    {
        $notify = $this->createMock(NotifyInterface::class);
        $notify
            ->expects($this->exactly(2))
            ->method('notify')
            ->willReturn(null);

        $pool = new NotifyPool();
        $pool->subscribe($notify);
        $pool->subscribe($notify);

        $pool->notify(new Exception(), 704);
    }

    public function testSentryNotifier()
    {
        try {
            $notifier = new SentryNotify([]);
            $notifier->notify(new Exception(), 704);
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->assertTrue(false);
        }
    }
}
