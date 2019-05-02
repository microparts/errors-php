<?php declare(strict_types=1);

namespace Microparts\Errors\Tests;

use Exception;
use Microparts\Errors\Error;
use Microparts\Errors\Formatter\DefaultErrorFormatter;
use Microparts\Errors\Handler\DefaultErrorHandler;
use Microparts\Errors\Notify\NullNotify;
use Microparts\Errors\TypeOfException;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ErrorTest extends TestCase
{
    public function testHowClassCreateNewInstanceWithoutArgs()
    {
        try {
            new Error();
            $this->assertTrue(true);
        } catch (Throwable $e) {
            printf($e);
            $this->assertTrue(false);
        }
    }

    public function testHowItWorksTogether()
    {
        $error = new Error($debug = true);
        $error->addNotifier(new NullNotify());
        $error->addDatabaseException(PDOException::class);
        $error->addMaskedException(MaskedException::class);
        $error->addException(new TypeOfException(Exception::class, true, true, 'custom message'));
        $error->addSilentException(SilentException::class);
        $error->setHandler(new DefaultErrorHandler());
        $error->setFormatter(new DefaultErrorFormatter($debug));

        try {
            throw new PDOException('test');
        } catch (Throwable $e) {
            $response = $error->capture($e);
            $this->check($response, TypeOfException::MASKED_DATABASE_MESSAGE);
        }

        try {
            throw new MaskedException('test');
        } catch (Throwable $e) {
            $response = $error->capture($e);
            $this->check($response, TypeOfException::MASKED_DEFAULT_MESSAGE);
        }

        try {
            throw new Exception('test');
        } catch (Throwable $e) {
            $response = $error->capture($e);
            $this->check($response, 'custom message');
        }

        try {
            throw new SilentException('test');
        } catch (Throwable $e) {
            $response = $error->capture($e);
            $this->check($response, 'test');
        }
    }

    public function testStaticMethod()
    {
        $error = Error::new();

        try {
            throw new PDOException('test');
        } catch (Throwable $e) {
            $response = $error->capture($e);
            $this->check($response, TypeOfException::MASKED_DATABASE_MESSAGE);
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param $message
     */
    private function check(ResponseInterface $response, string $message = '')
    {
        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents); // seek(0)

        $array = json_decode($contents, true);

        $this->assertSame($array['error']['code'], '0');
        $this->assertSame($array['error']['message'], $message);
        $this->assertSame($array['error']['status_code'], 500);
        $this->assertIsArray($array['error']['debug']);
        $this->assertNotEmpty($array['error']['debug']);
    }
}

class MaskedException extends PDOException {}
class SilentException extends Exception {}
