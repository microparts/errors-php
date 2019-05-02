<?php declare(strict_types=1);

namespace Microparts\Errors\Tests\Validation;

use ArrayIterator;
use IteratorAggregate;
use Microparts\Errors\Tests\TestCase;
use Microparts\Errors\Validation\ErrorBag;

class ErrorBagTest extends TestCase
{
    public function testHowClassCreateNewInstanceWithoutArgs()
    {
        try {
            new ErrorBag();
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            printf($e);
            $this->assertTrue(false);
        }
    }

    public function testShouldBeImplementIteratorAggregate()
    {
        $bag = new ErrorBag();

        $this->assertInstanceOf(IteratorAggregate::class, $bag);
        $this->assertInstanceOf(ArrayIterator::class, $bag->getIterator());
    }

    public function testHowItWorksWithMessages()
    {
        $bag = new ErrorBag();
        $bag->addMessage('auth.password', ['some validation rule']);

        $this->assertIsArray($bag->getMessage('auth.password'));
        $this->assertSame($bag->getMessage('auth.password'), ['some validation rule']);
        $this->assertIsArray($bag->all());

        foreach ($bag as $item) {
            $this->assertSame($item, ['some validation rule']);
        }
    }
}
