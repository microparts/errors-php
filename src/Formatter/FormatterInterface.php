<?php declare(strict_types=1);

namespace Microparts\Errors\Formatter;

use Microparts\Errors\Validation\ErrorBag;
use Throwable;

interface FormatterInterface
{
    /**
     * Called after handle any exception, except ValidationException.
     *
     * @param \Throwable|null $e
     * @param string $code
     * @param string $message
     * @param int $statusCode
     *
     * @return array
     */
    public function default(Throwable $e, $code, $message, $statusCode = 500): array;

    /**
     * Called only for validation.
     * Hard tied with ValidationException.
     *
     * @param \Microparts\Errors\Validation\ErrorBag $validation
     * @param string $code
     * @param string $message
     *
     * @return array
     */
    public function validation(ErrorBag $validation, $code, $message): array;
}
