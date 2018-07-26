<?php

namespace App\Tests\Controller;

use App\Application\Connection\Connection;
use App\Application\Connection\ConnectionProvider;
use App\Application\Connection\DTO\ApiResponse;
use App\Controller\FrontController;
use App\SocialMedia\Common\Client;
use App\SocialMedia\Common\SocialFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class FrontControllerTest extends TestCase
{
    /** @var FrontController */
    private $frontController;
    /** @var Request | MockObject */
    private $request;
    /** @var SocialFactory | MockObject */
    private $factory;


    protected function setUp()
    {
        $this->frontController = new FrontController();
        $this->frontController->setContainer($this->createMock(ContainerInterface::class));

        $this->request = $this->createMock(Request::class);

        $this->factory = $this->createMock(SocialFactory::class);
    }


    public function testOnFailedDispatch()
    {
        $this->request->method('get')->willThrowException(new \Exception());
        $result = $this->frontController->dispatch($this->request, $this->factory);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testDispatch()
    {
        $this->request->method('get')->willReturn('fakeMethod');
        $this->request->query = $this->createMock(ParameterBag::class);



        $fakeClient = $this->createMock(FakeClient::class);
        $response = $this->createMock(ApiResponse::class);

        $fakeClient->method('fakeMethod')->willReturn($response);
        $response->method('getHttpCode')->willReturn(200);

        $this->factory->method('loadSocialMediaClient')
            ->willReturn($fakeClient);

        $result = $this->frontController->dispatch($this->request, $this->factory);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());

    }
}


class FakeClient implements Client
{
    public function __construct(ParameterBag $parameterBag, ConnectionProvider $connectionProvider) {}

    public function fakeMethod(){}

    public function apiCall(string $url, array $parameters = [], string $httpVerb = Connection::HTTP_GET): ApiResponse
    {

    }

}