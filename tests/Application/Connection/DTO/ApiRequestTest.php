<?php

namespace App\Tests\Application\Connection\DTO;

use App\Application\Connection\Connection;
use App\Application\Connection\DTO\ApiRequest;
use PHPUnit\Framework\TestCase;

class ApiRequestTest extends TestCase
{
    /** @var ApiRequest */
    private $apiRequest;

    protected function setUp()
    {
        $this->apiRequest = new ApiRequest();
    }

    public function testGetSetAuthParams()
    {
        $authArray = ['oauth_key' => 'oauth_value'];
        $this->assertEquals($this->apiRequest->getAuthParams(), []);
        $this->apiRequest->setAuthParams($authArray);
        $this->assertEquals($this->apiRequest->getAuthParams(), $authArray);
    }


    public function testGetFullUrl()
    {
        $url = 'http://www.google.es';
        $params = ['key1' => 'value1', 'key2' => 'value2'];
        $this->assertEquals($this->apiRequest->getParameters(), []);
        $this->assertEmpty($this->apiRequest->getUrl());
        $this->apiRequest->setUrl($url);
        $this->assertEquals($this->apiRequest->getFullUrl(), $url);
        $this->apiRequest->setParameters($params);
        $expectedFullUrl = $url.'?'.http_build_query($params, null,'&', PHP_QUERY_RFC3986);
        $this->assertEquals($this->apiRequest->getFullUrl(), $expectedFullUrl);
    }

    public function testGetSetAddParameters()
    {
        $this->assertEquals($this->apiRequest->getParameters(), []);
        $baseParamArray = ['key1' => 'value1', 'key2' => 'value2'];
        $this->apiRequest->setParameters($baseParamArray);
        $this->assertEquals($this->apiRequest->getParameters(), $baseParamArray);
        $this->apiRequest->addParameter('key3','value3');
        $this->assertEquals(
            $this->apiRequest->getParameters(),
            $baseParamArray+['key3' => 'value3'],
            'addParameter must concat to existing parameters'
        );
    }

    public function testGetSetHttpVerb()
    {
        $this->assertEquals(
            $this->apiRequest->getHttpVerb(),
            Connection::HTTP_GET,
            'Default http verb must be HTTP_GET'
        );
        $this->apiRequest->setHttpVerb(Connection::HTTP_POST);
        $this->assertEquals(
            $this->apiRequest->getHttpVerb(),
            Connection::HTTP_POST
        );
    }

    public function testAppendParameters()
    {
        $this->assertEquals($this->apiRequest->getParameters(), []);
        $initialParams = ['key1' => 'value1', 'key2' => 'value2'];
        $this->apiRequest->setParameters($initialParams);
        $this->assertEquals($this->apiRequest->getParameters(), $initialParams);
        $secondParams = ['foo' => 'bar', 'baz' => 'nep nep'];
        $this->apiRequest->appendParameters($secondParams);
        $this->assertEquals($this->apiRequest->getParameters(), $initialParams+$secondParams);
    }

    public function testGetSetAuthString()
    {
        $authString = 'Authorization: OAuth';
        $this->assertEmpty($this->apiRequest->getAuthString());
        $this->apiRequest->setAuthString($authString);
        $this->assertEquals($this->apiRequest->getAuthString(), $authString);
    }

    public function testGetSetUrl()
    {
        $url = 'http://www.google.es';
        $this->assertEmpty($this->apiRequest->getUrl());
        $this->apiRequest->setUrl($url);
        $this->assertEquals($this->apiRequest->getURL(), $url);
    }
}
