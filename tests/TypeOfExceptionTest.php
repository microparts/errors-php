<?php declare(strict_types=1);

namespace Microparts\Errors\Tests;

use Microparts\Errors\TypeOfException;

class TypeOfExceptionTest extends TestCase
{
    public function testDefaultValues()
    {
        $toe = new TypeOfException('name');

        $this->assertSame($toe->getName(), 'name');
        $this->assertSame($toe->getMaskedMessage(), TypeOfException::MASKED_DEFAULT_MESSAGE);
        $this->assertFalse($toe->isMasked());
        $this->assertFalse($toe->isSilent());
    }
}
