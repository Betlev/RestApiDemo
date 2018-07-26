<?php

namespace App\Tests\Application\Input;

use App\Application\Input\ParameterExtractor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class ParameterExtractorTest extends TestCase
{
    /** @var ParameterBag|MockObject */
    private $bag;
    /** @var array */
    private$required;

    protected function setUp()
    {
        $this->bag = $this->createMock(ParameterBag::class);
        $this->required = ['item1', 'item2'];
    }


    public function testExtractRequired()
    {
        $this->bag->method('has')->willReturn(true);
        // because we are using a mocked bag, the returned array contains null as values
        $extractedParams = ParameterExtractor::extractRequired($this->required, $this->bag);
        $this->assertSame($this->required, array_keys($extractedParams));
    }

    public function testExtractRequiredThrowsExceptionOnParameterNotFound()
    {
        $this->bag->method('has')->willReturn(false);
        $this->expectException(\Exception::class);
        \App\Application\Input\ParameterExtractor::extractRequired($this->required, $this->bag);
    }
}
