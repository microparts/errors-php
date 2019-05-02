<?php declare(strict_types=1);

namespace Microparts\Errors\Validation;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

final class ErrorBag implements IteratorAggregate
{
    /**
     * @var array
     */
    private $messages = [];

    /**
     * Add message to Bag with following format:
     *
     * $bag->addMessage('auth.[0].password', ['validation err 1', 'validation error2'])
     *
     * @param string $path — dot notation format
     * @param array $values
     */
    public function addMessage(string $path, array $values)
    {
        $this->messages[$path] = array_values($values);
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function getMessage(string $key): array
    {
        return $this->messages[$key];
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->messages;
    }

    /**
     * Retrieve an external iterator
     *
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @since 5.0.0
     *
     * @return Traversable An instance of an object implementing Iterator or Traversable
     */
    public function getIterator(): iterable
    {
        return new ArrayIterator($this->messages);
    }
}
