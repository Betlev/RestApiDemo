<?php

namespace App\Tests\Application\Connection;

use App\Application\Connection\Auth;
use App\Application\Connection\Connection;
use App\Application\Connection\ConnectionFactory;
use App\Application\Connection\ConnectionProvider;
use App\Application\Connection\DTO\ApiRequest;
use App\Application\Connection\DTO\ApiResponse;
use App\Application\Connection\DTO\DataTransferFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionProviderTest extends TestCase
{
    /** @var DataTransferFactory | MockObject */
    private $dtoFactory;
    /** @var ConnectionFactory | MockObject */
    private $connectionFactory;
    /** @var ConnectionProvider */
    private $connectionProvider;
    /** @var ApiRequest | MockObject */
    private $apiRequest;
    /** @var Connection | MockObject */
    private $connection;
    /** @var Auth | MockObject */
    private $auth;

    protected function setUp()
    {
        $this->connection = $this->createMock(Connection::class);
        $this->connectionFactory = $this->createMock(ConnectionFactory::class);
        $this->connectionFactory->method('getConnection')->willReturn($this->connection);

        $this->apiRequest = $this->createMock(ApiRequest::class);
        $this->dtoFactory = $this->createMock(DataTransferFactory::class);
        $this->dtoFactory->method('createRequest')->willReturn($this->apiRequest);

        $this->auth = $this->createMock(Auth::class);

        $this->connectionProvider = new ConnectionProvider($this->dtoFactory, $this->connectionFactory);
    }


    public function testIsConnectionReady()
    {
        $this->assertFalse($this->connectionProvider->isConnectionReady(), 'Connection must be requested on execute call');
        $this->connectionProvider->execute();
        $this->assertTrue($this->connectionProvider->isConnectionReady(), 'Connection must be retained for subsequent calls');
    }

    public function testReset()
    {
        $this->connectionProvider->withSignature($this->auth);
        $this->connectionProvider->execute();
        $this->assertTrue($this->connectionProvider->isRequestSigned());
        $this->assertTrue($this->connectionProvider->isConnectionReady());
        $this->dtoFactory->expects($this->once())->method('createRequest');
        $this->connectionProvider->reset();
        $this->assertFalse($this->connectionProvider->isRequestSigned(), 'Auth instance must be reset');
        $this->assertFalse($this->connectionProvider->isConnectionReady(), 'Connection instance must be reset');
    }

    public function testIsRequestSigned()
    {
        $this->assertFalse($this->connectionProvider->isRequestSigned(), 'Request must be unsigned on startup');
        $this->connectionProvider->withSignature($this->auth);
        $this->assertTrue($this->connectionProvider->isRequestSigned());
    }

    public function testQueryParameters()
    {
        $this->apiRequest->expects($this->once())->method('setParameters');
        $result = $this->connectionProvider->queryParameters(['key1' => 'value1']);
        $this->assertSame($this->connectionProvider, $result, 'Must return self instance');
    }

    public function testHttpVerb()
    {
        $this->apiRequest->expects($this->once())->method('setHttpVerb');
        $result = $this->connectionProvider->httpVerb(Connection::HTTP_PUT);
        $this->assertSame($this->connectionProvider, $result, 'Must return self instance');
    }

    public function testWithSignature()
    {
        $result = $this->connectionProvider->withSignature($this->auth);
        $this->assertSame($this->connectionProvider, $result, 'Must return self instance');
        $this->assertTrue($this->connectionProvider->isRequestSigned());
    }

    public function testDestination()
    {
        $this->apiRequest->expects($this->once())->method('setUrl');
        $result = $this->connectionProvider->destination('http://www.google.es');
        $this->assertSame($this->connectionProvider, $result, 'Must return self instance');
    }

    public function testExecute()
    {
        $this->configureConnectionFactory();
        $this->configureConnection();

        $result = $this->connectionProvider->execute();
        $this->assertInstanceOf(ApiResponse::class, $result);
        $this->assertTrue($this->connectionProvider->isConnectionReady());
    }


    public function testExecuteWithSignature()
    {
        $this->configureConnectionFactory();
        $this->configureConnection();

        $this->auth->expects($this->once())->method('sign');
        $this->connectionProvider->withSignature($this->auth);
        $result = $this->connectionProvider->execute();
        $this->assertInstanceOf(ApiResponse::class, $result);
    }

    public function configureConnectionFactory(): void
    {
        $this->connectionFactory->expects($this->once())
            ->method('getConnection')
            ->with(
                $this->equalTo(ConnectionProvider::TYPE_CURL)
            )
            ->willReturn($this->connection);
    }

    public function configureConnection(): void
    {
        $this->connection->expects($this->once())->method('configure');
        $this->connection->expects($this->once())
            ->method('call')
            ->willReturn($this->createMock(ApiResponse::class));
        $this->connection->expects($this->once())->method('close');
    }


}
