<?php

namespace App\SocialMedia\Twitter\Client;


use App\Application\Connection\Connection;
use App\Application\Connection\ConnectionProvider;
use App\Application\Connection\DTO\ApiResponse;
use App\Application\Input\ParameterExtractor;
use App\SocialMedia\Common\Client;
use App\SocialMedia\Twitter\Auth\TwitterAuth;
use Symfony\Component\HttpFoundation\ParameterBag;

class TwitterClient implements Client
{

    public const TWITTER_ENDPOINT = 'https://api.twitter.com/1.1/';

    /**
     * @var ParameterBag
     */
    private $parameterBag;
    /**
     * @var ConnectionProvider
     */
    private $connectionProvider;

    /**
     * TwitterClient constructor.
     * @param ParameterBag $parameterBag
     * @param ConnectionProvider $connectionProvider
     */
    public function __construct(ParameterBag $parameterBag, ConnectionProvider $connectionProvider)
    {
        $this->parameterBag = $parameterBag;
        $this->connectionProvider = $connectionProvider;
    }

    /**
     * @return ApiResponse
     * @throws \Exception
     */
    public function lastTweetsFromUser(): ApiResponse
    {
        $params = ParameterExtractor::extractRequired(['username', 'num_tweets'], $this->parameterBag);
        $requestParams = [
            'screen_name' => $params['username'],
            'count' => $params['num_tweets']
        ];

        return $this->apiCall('statuses/user_timeline.json', $requestParams);
    }

    /**
     * @param string $url
     * @param array $parameters
     * @param string $httpVerb
     * @return ApiResponse
     * @throws \App\Application\Connection\Exception\ConnectionNotFoundException
     */
    public function apiCall(string $url, array $parameters = [], string $httpVerb = Connection::HTTP_GET) :ApiResponse
    {
        return $this->connectionProvider
            ->httpVerb($httpVerb)
            ->destination(self::TWITTER_ENDPOINT.$url)
            ->queryParameters($parameters)
            ->withSignature(new TwitterAuth())
            ->execute();
    }
}