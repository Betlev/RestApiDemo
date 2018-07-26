<?php

namespace App\Tests\Application\Connection\DTO;

use App\Application\Connection\DTO\ApiResponse;
use PHPUnit\Framework\TestCase;

class ApiResponseTest extends TestCase
{
    /** @var ApiResponse */
    private $apiResponse;
    /** @var array */
    private $responseValues;

    public function setUp()
    {
        $this->responseValues = [
            'http-code' => 200,
            'content-type' => 'application/json',
            'content' => json_encode(['foo' => 'bar']),
            'date' => new \DateTime()
        ];

        $this->apiResponse = $this->createResponse();
    }


    public function createResponse(int $responseCode = 200): ApiResponse
    {
        return new ApiResponse(
            $responseCode,
            $this->responseValues['content-type'],
            $this->responseValues['content'],
            $this->responseValues['date']
        );
    }

    public function testCreateFromCurl()
    {
        $this->responseValues['date'] = 'now';
        $result = ApiResponse::createFromCurl($this->responseValues);
        $this->assertInstanceOf(ApiResponse::class, $result);
    }

    public function testIsResponseOk()
    {
        $this->assertTrue($this->apiResponse->isResponseOk());
        $apiResponse = $this->createResponse(500);
        $this->assertFalse($apiResponse->isResponseOk());
    }

    public function testGetDate()
    {
        $this->assertInstanceOf(\DateTime::class, $this->apiResponse->getDate());
    }

    public function testGetHttpCode()
    {
        $this->assertSame($this->responseValues['http-code'], $this->apiResponse->getHttpCode());
    }

    public function testGetContentType()
    {
        $this->assertSame($this->responseValues['content-type'], $this->apiResponse->getContentType());
    }

    public function testGetContent()
    {
        $this->assertSame(json_decode($this->responseValues['content'], true), $this->apiResponse->getContent());
    }

    public function testGetErrorMessage()
    {
        $apiResponse = $this->createResponse(500);
        $this->assertSame('Internal Server Error from Endpoint', $apiResponse->getErrorMessage()[0]);
    }

    public function testGetErrorMessageAsJson()
    {
        $apiResponse = $this->createResponse(500);
        $this->assertSame(json_encode(['Internal Server Error from Endpoint']), $apiResponse->getErrorMessageAsJson());
    }
}
