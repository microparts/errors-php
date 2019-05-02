<?php declare(strict_types=1);

namespace Microparts\Errors\Formatter;

use Microparts\Errors\Validation\ErrorBag;
use Throwable;

final class DefaultErrorFormatter implements FormatterInterface
{
    /**
     * Show exception trace or not.
     *
     * @var bool
     */
    private $debug;

    /**
     * DefaultErrorFormatter constructor.
     *
     * @param bool $debug
     */
    public function __construct(bool $debug = true)
    {
        $this->debug = $debug;
    }

    /**
     * Called after handle any exception except, ValidationException.
     *
     * @param \Throwable $e
     * @param $code
     * @param string $message
     * @param int $statusCode
     *
     * @return array
     */
    public function default(Throwable $e, $code, $message, $statusCode = 500): array
    {
        $array = [
            'error' => [
                'code'        => (string) $code,
                'message'     => (string) $message,
                'status_code' => (int) $statusCode,
            ]
        ];

        if ($this->debug) {
            $array['error']['debug'] = [
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
                'class' => get_class($e),
                'trace' => $e->getTrace(),
            ];
        }

        return $array;
    }

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
    public function validation(ErrorBag $validation, $code, $message): array
    {
        return [
            'error' => [
                'code'        => (string) $code,
                'message'     => (string) $message,
                'status_code' => 422, // standard http validation code.
                'validation'  => $validation->all(),
            ]
        ];
    }
}
