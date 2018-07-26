<?php

namespace App\Tests\SocialMedia\Twitter\Client;

use App\Application\Connection\Connection;
use App\Application\Connection\ConnectionProvider;
use App\Application\Connection\DTO\ApiResponse;
use App\SocialMedia\Twitter\Auth\TwitterAuth;
use App\SocialMedia\Twitter\Client\TwitterClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class TwitterClientTest extends TestCase
{
    /** @var ParameterBag|MockObject */
    private $parameterBag;
    /** @var ConnectionProvider|MockObject */
    private $connectionProvider;

    protected function setUp()
    {
        $this->parameterBag = $this->createMock(ParameterBag::class);
        $this->connectionProvider = $this->createMock(ConnectionProvider::class);
    }

    public function testLastTweetsFromUser()
    {
        $this->parameterBag->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(
                $this->equalTo('username'),
                $this->equalTo('num_tweets')
            )
            ->willReturn(true);

        /** @var TwitterClient|MockObject $twitterClient */
        $twitterClient = $this->getMockBuilder(TwitterClient::class)
            ->setConstructorArgs([$this->parameterBag, $this->connectionProvider])
            ->setMethods(['apiCall'])
            ->getMock();


        $twitterClient->expects($this->once())
            ->method('apiCall')
            ->with(
                $this->equalTo('statuses/user_timeline.json'),
                $this->callback(function($argument){
                        return is_array($argument) && array_keys($argument) === ['screen_name', 'count'];
                })
            )
        ->willReturn($this->createMock(ApiResponse::class));

        $result = $twitterClient->lastTweetsFromUser();

        $this->assertInstanceOf(ApiResponse::class, $result);
    }

    public function testApiCall()
    {
        $destination = 'foo/bar';
        $parameters = [];
        $this->connectionProvider->expects($this->once())
            ->method('httpVerb')
            ->with($this->equalTo(Connection::HTTP_GET))
            ->willReturn($this->connectionProvider);
        $this->connectionProvider->expects($this->once())
            ->method('destination')
            ->with($this->equalTo(TwitterClient::TWITTER_ENDPOINT.$destination))
            ->willReturn($this->connectionProvider);
        $this->connectionProvider->expects($this->once())
            ->method('queryParameters')
            ->with($this->isType('array'))
            ->willReturn($this->connectionProvider);
        $this->connectionProvider->expects($this->once())
            ->method('withSignature')
            ->with($this->isInstanceOf(TwitterAuth::class))
            ->willReturn($this->connectionProvider);
        $this->connectionProvider->expects($this->once())
            ->method('execute')
            ->willReturn($this->createMock(ApiResponse::class));

        $twitterClient = new TwitterClient($this->parameterBag, $this->connectionProvider);
        $result = $twitterClient->apiCall($destination, $parameters);
        $this->assertInstanceOf(ApiResponse::class, $result);
    }
}
