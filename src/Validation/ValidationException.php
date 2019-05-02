<?php declare(strict_types=1);

namespace Microparts\Errors\Validation;

use LogicException;
use Throwable;

final class ValidationException extends LogicException
{
    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var \Throwable
     */
    private $previous;

    /**
     * @var ErrorBag
     */
    private $errors;

    /**
     * ValidationException constructor.
     *
     * @param \Microparts\Errors\Validation\ErrorBag $errors
     * @param int $statusCode
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        ErrorBag $errors,
        int $statusCode = 422,
        string $message = 'Validation error.',
        int $code = 0,
        Throwable $previous = null
    )
    {
        $this->errors     = $errors;
        $this->statusCode = $statusCode;
        $this->message    = $message;
        $this->code       = $code;
        $this->previous   = $previous;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ErrorBag
     */
    public function getErrors(): ErrorBag
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
