<?php declare(strict_types=1);

namespace Microparts\Errors\Tests\Formatter;

use Microparts\Errors\Formatter\DefaultErrorFormatter;
use Microparts\Errors\Tests\TestCase;
use Microparts\Errors\Validation\ErrorBag;

class DefaultErrorFormatterTest extends TestCase
{
    public function testHowClassCreateNewInstanceWithoutArgs()
    {
        try {
            new DefaultErrorFormatter();
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            printf($e);
            $this->assertTrue(false);
        }
    }

    public function testDefaultStructureWithoutDebug()
    {
        $expected = [
            'error' => [
                'code'        => '1',
                'message'     => 'test',
                'status_code' => 500
            ]
        ];

        $formatter = new DefaultErrorFormatter(false);
        $results   = $formatter->default(new \Exception(), 1, 'test');

        $this->assertIsArray($results);
        $this->assertSame($expected, $results);
    }

    public function testDefaultStructureWithDebug()
    {
        $formatter = new DefaultErrorFormatter(true);
        $results   = $formatter->default(new \Exception(), 1, 'test');

        $this->assertIsArray($results);
        $this->assertSame($results['error']['code'], '1');
        $this->assertSame($results['error']['message'], 'test');
        $this->assertSame($results['error']['status_code'], 500);
        $this->assertIsArray($results['error']['debug']);
        $this->assertIsInt($results['error']['debug']['line']);
        $this->assertIsString($results['error']['debug']['file']);
        $this->assertIsString($results['error']['debug']['class']);
        $this->assertIsArray($results['error']['debug']['trace']);
    }

    public function testValidationStructureOutput()
    {
        $bag = new ErrorBag();
        $bag->addMessage('auth.password', ['field is required']);

        $formatter = new DefaultErrorFormatter(false);
        $results   = $formatter->validation($bag, 1, 'test');

        $this->assertIsArray($results);
        $this->assertSame($results['error']['code'], '1');
        $this->assertSame($results['error']['message'], 'test');
        $this->assertSame($results['error']['status_code'], 422);

        $this->assertIsArray($results['error']['validation']);
        $this->assertSame($results['error']['validation']['auth.password'], ['field is required']);
    }
}
