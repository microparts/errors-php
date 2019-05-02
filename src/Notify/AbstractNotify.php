<?php declare(strict_types=1);

namespace Microparts\Errors\Notify;

use Throwable;

abstract class AbstractNotify implements NotifyInterface
{
    /**
     * Convert exception to array without message.
     *
     * @param \Throwable $e
     *
     * @return array
     */
    protected function toArray(Throwable $e)
    {
        return [
            'code'  => $e->getCode(),
            'line'  => $e->getLine(),
            'file'  => $e->getFile(),
            'class' => get_class($e),
            'trace' => $e->getTrace(),
        ];
    }
}
