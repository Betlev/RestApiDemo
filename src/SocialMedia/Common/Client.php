<?php

namespace App\SocialMedia\Common;


use App\Application\Connection\Connection;
use App\Application\Connection\ConnectionProvider;
use App\Application\Connection\DTO\ApiResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

interface Client
{
    public function __construct(ParameterBag $parameterBag, ConnectionProvider $connectionProvider);

    public function apiCall(string $url, array $parameters = [], string $httpVerb = Connection::HTTP_GET) :ApiResponse;
}