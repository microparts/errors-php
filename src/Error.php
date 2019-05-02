<?php declare(strict_types=1);

namespace Microparts\Errors;

use Microparts\Errors\Formatter\DefaultErrorFormatter;
use Microparts\Errors\Formatter\FormatterInterface;
use Microparts\Errors\Handler\DefaultErrorHandler;
use Microparts\Errors\Handler\HandlerInterface;
use Microparts\Errors\Notify\NotifyInterface;
use Microparts\Errors\Notify\NotifyPool;
use Microparts\Errors\Validation\ValidationException;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use SplQueue;
use Throwable;

final class Error
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * Exceptions queue.
     *
     * @var \SplQueue
     */
    private $typeOfExceptions;

    /**
     * Formatter class which is responsible for formatting response.
     *
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * Handler class which is responsible for converting
     * handled exception to PSR ResponseInterface.
     *
     * @var HandlerInterface
     */
    private $handler;

    /**
     * Notifier class which allows send messages to
     * multiple channels (any: like sentry, bugsnag, stdout).
     *
     * @var NotifyInterface
     */
    private $notifier;

    /**
     * Error constructor.
     *
     * @param bool $debug
     */
    public function __construct(bool $debug = true)
    {
        $this->debug = $debug;
        $this->typeOfExceptions = new SplQueue();
        $this->notifier = new NotifyPool();
    }

    /**
     * Create instance with default preferences.
     *
     * @param bool $debug
     *
     * @return \Microparts\Errors\Error
     */
    public static function new(bool $debug = true)
    {
        $error = new Error($debug);
        $error->setFormatter(new DefaultErrorFormatter($debug));
        $error->setHandler(new DefaultErrorHandler());
        $error->addDatabaseException(PDOException::class);
        $error->addSilentException(ValidationException::class);

        return $error;
    }

    /**
     * Add Notifier class for send message
     * about exception to supported channels.
     *
     * @param \Microparts\Errors\Notify\NotifyInterface $notify
     *
     * @return $this
     */
    public function addNotifier(NotifyInterface $notify): self
    {
        $this->notifier->subscribe($notify);

        return $this;
    }

    /**
     * Add exception who will be hidden from output (logs or other).
     *
     * @param string $e
     *
     * @return \Microparts\Errors\Error
     */
    public function addSilentException(string $e): self
    {
        $this->typeOfExceptions->push(new TypeOfException($e, true));

        return $this;
    }

    /**
     * Add exception string to replace a thrown exception message.
     * For security reason. For example: any app works with
     * database must be hide PDOException from user output.
     *
     * @param string $e
     * @param bool $silent
     *
     * @return \Microparts\Errors\Error
     */
    public function addMaskedException(string $e, bool $silent = false): self
    {
        $this->typeOfExceptions->push(new TypeOfException($e, $silent, true));

        return $this;
    }

    /**
     * Add database exception with behavior similar that masked exception type.
     *
     * @param string $e
     * @param bool $silent
     *
     * @return \Microparts\Errors\Error
     */
    public function addDatabaseException(string $e, bool $silent = false): self
    {
        $item = new TypeOfException($e, $silent, true, TypeOfException::MASKED_DATABASE_MESSAGE);
        $this->typeOfExceptions->push($item);

        return $this;
    }

    /**
     * Raw method for other types.
     *
     * @param \Microparts\Errors\TypeOfException $e
     *
     * @return \Microparts\Errors\Error
     */
    public function addException(TypeOfException $e): self
    {
        $this->typeOfExceptions->push($e);

        return $this;
    }

    /**
     * Add formatter class which is
     * responsible for formatting response.
     *
     * @param \Microparts\Errors\Formatter\FormatterInterface $formatter
     *
     * @return \Microparts\Errors\Error
     */
    public function setFormatter(FormatterInterface $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Add handler class which is responsible for converting
     * handled exception to PSR ResponseInterface.
     *
     * @param \Microparts\Errors\Handler\HandlerInterface $handler
     *
     * @return \Microparts\Errors\Error
     */
    public function setHandler(HandlerInterface $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Capture the exception and handle it.
     *
     * @param \Throwable $e
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function capture(Throwable $e): ResponseInterface
    {
        return $this->handler->handle(
            $e, $this->typeOfExceptions, $this->formatter, $this->notifier
        );
    }
}
