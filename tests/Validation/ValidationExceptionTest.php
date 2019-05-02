<?php declare(strict_types=1);

namespace Microparts\Errors\Tests\Validation;

use Microparts\Errors\Tests\TestCase;
use Microparts\Errors\Validation\ErrorBag;
use Microparts\Errors\Validation\ValidationException;

class ValidationExceptionTest extends TestCase
{
    public function testHowValidationExceptionWorks()
    {
        $bag = new ErrorBag();
        $bag->addMessage('auth.password', ['some validation rule']);

        try {
            throw new ValidationException($bag);
        } catch (ValidationException $e) {
            $this->assertInstanceOf(ErrorBag::class, $e->getErrors());
            $this->assertSame(422, $e->getStatusCode());
            $this->assertSame('Validation error.', $e->getMessage());
        }
    }
}
