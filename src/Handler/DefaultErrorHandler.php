<?php declare(strict_types=1);

namespace Microparts\Errors\Handler;

use Microparts\Errors\Formatter\FormatterInterface;
use Microparts\Errors\Notify\NotifyPool;
use Microparts\Errors\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use SplQueue;
use Throwable;
use Zend\Diactoros\Response;

final class DefaultErrorHandler implements HandlerInterface
{
    /**
     * Handles the exceptions based on it types and preferences.
     *
     * @param \Throwable $e
     * @param \SplQueue $queue
     * @param \Microparts\Errors\Formatter\FormatterInterface $formatter
     * @param \Microparts\Errors\Notify\NotifyPool $pool
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(Throwable $e, SplQueue $queue, FormatterInterface $formatter, NotifyPool $pool): ResponseInterface
    {
        $message = null;
        $silent = false;

        while (!$queue->isEmpty()) {
            /** @var \Microparts\Errors\TypeOfException $toe */
            $toe = $queue->dequeue();

            if (get_class($e) === $toe->getName()) {
                $silent = $toe->isSilent();
                $message = $toe->isMasked() ? $toe->getMaskedMessage() : null;
                break;
            }
        }

        $msg = $message ?? $e->getMessage();

        if (!$silent) {
            $pool->notify($e, $this->getStatusCode($e));
        }

        if ($e instanceof ValidationException) {
            return $this->respond(
                $formatter->validation($e->getErrors(), $e->getCode(), $msg),
                $e->getStatusCode()
            );
        }

        $status = $this->getStatusCode($e);
        $body = $formatter->default($e, $e->getCode(), $msg, $status);

        return $this->respond($body, $status);
    }

    /**
     * Get http status code for response.
     *
     * @param \Throwable $e
     *
     * @return int
     */
    private function getStatusCode(Throwable $e)
    {
        $status = 500;

        if (method_exists($e, 'getStatusCode')) {
            $status = $e->getStatusCode();
        }

        return $status;
    }

    /**
     * Creates response for an error. Used zend/diactoros.
     *
     * @param array $value
     * @param int $status
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function respond(array $value, int $status): ResponseInterface
    {
        $body = json_encode($value, JSON_UNESCAPED_UNICODE);

        $response = new Response();
        $response->getBody()->write($body);
        $response->getBody()->seek(0);
        $response->withHeader('Content-Type', 'application/json');
        $response->withStatus($status);

        return $response;
    }
}
