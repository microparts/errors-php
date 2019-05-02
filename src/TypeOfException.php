<?php declare(strict_types=1);

namespace Microparts\Errors;

final class TypeOfException
{
    public const MASKED_DEFAULT_MESSAGE  = 'Error occurred. Message hidden for security reason.';
    public const MASKED_DATABASE_MESSAGE = 'Database error occurred. See logs for details.';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var bool
     */
    private $silent = false;

    /**
     * @var bool
     */
    private $masked = false;

    /**
     * @var string
     */
    private $maskedMessage = self::MASKED_DEFAULT_MESSAGE;

    /**
     * TypeOfException constructor.
     *
     * @param string $name
     * @param bool $silent
     * @param bool $masked
     * @param string $maskedMessage
     */
    public function __construct(
        string $name,
        bool $silent = false,
        bool $masked = false,
        string $maskedMessage = self::MASKED_DEFAULT_MESSAGE
    ) {
        $this->name          = $name;
        $this->silent        = $silent;
        $this->masked        = $masked;
        $this->maskedMessage = $maskedMessage;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isSilent(): bool
    {
        return $this->silent;
    }

    /**
     * @return bool
     */
    public function isMasked(): bool
    {
        return $this->masked;
    }

    /**
     * @return string
     */
    public function getMaskedMessage(): string
    {
        return $this->maskedMessage;
    }
}
