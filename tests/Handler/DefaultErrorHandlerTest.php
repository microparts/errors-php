<?php declare(strict_types=1);

namespace Microparts\Errors\Tests\Handler;

use Exception;
use Microparts\Errors\Formatter\DefaultErrorFormatter;
use Microparts\Errors\Handler\DefaultErrorHandler;
use Microparts\Errors\Notify\AbstractNotify;
use Microparts\Errors\Notify\NotifyPool;
use Microparts\Errors\Tests\TestCase;
use Microparts\Errors\TypeOfException;
use Microparts\Errors\Validation\ErrorBag;
use Microparts\Errors\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use SplQueue;
use Throwable;

class DefaultErrorHandlerTest extends TestCase
{
    public function testHowClassCreateNewInstanceWithoutArgs()
    {
        try {
            new DefaultErrorHandler();
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            printf($e);
            $this->assertTrue(false);
        }
    }

    public function testHowItHandleSimpleException()
    {
        $queue = new SplQueue();
        $pool = new NotifyPool();
        $formatter = new DefaultErrorFormatter();
        $handler = new DefaultErrorHandler();

        $response = $handler->handle(new Exception('Message', 10), $queue, $formatter, $pool);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents); // seek(0)

        $array = json_decode($contents, true);

        $this->assertSame($array['error']['code'], '10');
        $this->assertSame($array['error']['message'], 'Message');
        $this->assertSame($array['error']['status_code'], 500);
        $this->assertIsArray($array['error']['debug']);
        $this->assertNotEmpty($array['error']['debug']);
    }

    public function testHowItHandleWithNotifications()
    {
        global $notifyLog; // in the test cases bad practices is allowed :P

        $queue = new SplQueue();
        $queue->push(new TypeOfException(Exception::class));

        $pool = new NotifyPool();
        $pool->subscribe(new class extends AbstractNotify {
            public function notify(Throwable $e, int $status = 500): void {
                global $notifyLog;
                $notifyLog = [$this->toArray($e), $status];
            }
        });
        $formatter = new DefaultErrorFormatter();
        $handler   = new DefaultErrorHandler();

        $response = $handler->handle(new Exception('Message', 10), $queue, $formatter, $pool);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents); // seek(0)

        $array = json_decode($contents, true);

        $this->assertSame($array['error']['code'], '10');
        $this->assertSame($array['error']['message'], 'Message');
        $this->assertSame($array['error']['status_code'], 500);
        $this->assertIsArray($array['error']['debug']);
        $this->assertNotEmpty($array['error']['debug']);

        $this->assertNotEmpty($notifyLog);
        $this->assertIsScalar($notifyLog[0]['code']);
        $this->assertIsInt($notifyLog[0]['line']);
        $this->assertIsString($notifyLog[0]['file']);
        $this->assertIsString($notifyLog[0]['class']);
        $this->assertIsArray($notifyLog[0]['trace']);
        $this->assertNotEmpty($notifyLog[0]['trace']);
        $notifyLog = null;
    }

    public function testHowItHandleSilentExceptions()
    {
        global $notifyLog; // in the test cases bad practices is allowed :P

        $queue = new SplQueue();
        $queue->push(new TypeOfException(Exception::class, true));

        $pool = new NotifyPool();
        $pool->subscribe(new class extends AbstractNotify {
            public function notify(Throwable $e, int $status = 500): void {
                global $notifyLog;
                $notifyLog = [$this->toArray($e), $status];
            }
        });
        $formatter = new DefaultErrorFormatter();
        $handler   = new DefaultErrorHandler();

        $response = $handler->handle(new Exception('Message', 10), $queue, $formatter, $pool);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents); // seek(0)

        $array = json_decode($contents, true);

        $this->assertSame($array['error']['code'], '10');
        $this->assertSame($array['error']['message'], 'Message');
        $this->assertSame($array['error']['status_code'], 500);
        $this->assertIsArray($array['error']['debug']);
        $this->assertNotEmpty($array['error']['debug']);

        $this->assertNull($notifyLog);
    }

    public function testHowItHandleMaskedExceptions()
    {
        $queue = new SplQueue();
        $queue->push(new TypeOfException(Exception::class, true, true));

        $pool = new NotifyPool();
        $formatter = new DefaultErrorFormatter();
        $handler   = new DefaultErrorHandler();

        $response = $handler->handle(new Exception('SENSITIVE DATABASE MESSAGE WITH QUERY', 10), $queue, $formatter, $pool);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents); // seek(0)

        $array = json_decode($contents, true);

        $this->assertSame($array['error']['code'], '10');
        $this->assertSame($array['error']['message'], TypeOfException::MASKED_DEFAULT_MESSAGE);
        $this->assertSame($array['error']['status_code'], 500);
        $this->assertIsArray($array['error']['debug']);
        $this->assertNotEmpty($array['error']['debug']);
    }

    public function testHowItHandleExceptionsWithStatusCodeMethod()
    {
        $queue = new SplQueue();
        $pool = new NotifyPool();
        $formatter = new DefaultErrorFormatter();
        $handler   = new DefaultErrorHandler();

        $exception = new class extends Exception {
            public function getStatusCode() {
                return 418;
            }
        };

        $response = $handler->handle($exception, $queue, $formatter, $pool);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents); // seek(0)

        $array = json_decode($contents, true);

        $this->assertSame($array['error']['code'], '0');
        $this->assertSame($array['error']['message'], '');
        $this->assertSame($array['error']['status_code'], 418);
        $this->assertIsArray($array['error']['debug']);
        $this->assertNotEmpty($array['error']['debug']);
    }

    public function testHowItHandleValidationException()
    {
        $queue = new SplQueue();
        $pool = new NotifyPool();
        $formatter = new DefaultErrorFormatter();
        $handler   = new DefaultErrorHandler();
        $bag = new ErrorBag();
        $bag->addMessage('auth.password', ['some validation rule']);

        $response = $handler->handle(new ValidationException($bag), $queue, $formatter, $pool);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents); // seek(0)

        $array = json_decode($contents, true);

        $this->assertSame($array['error']['code'], '0');
        $this->assertSame($array['error']['message'], 'Validation error.');
        $this->assertSame($array['error']['status_code'], 422);
        $this->assertIsArray($array['error']['validation']);
        $this->assertSame($array['error']['validation']['auth.password'], ['some validation rule']);
    }
}
