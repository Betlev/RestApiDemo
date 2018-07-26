<?php

namespace App\Tests\Application\Connection\DTO;

use App\Application\Connection\DTO\ApiRequest;
use App\Application\Connection\DTO\DataTransferFactory;
use PHPUnit\Framework\TestCase;

class DataTransferFactoryTest extends TestCase
{

    public function testCreateRequest()
    {
        $dtoFactory = new DataTransferFactory();
        $request = $dtoFactory->createRequest();
        $this->assertInstanceOf(ApiRequest::class, $request);
    }
}
