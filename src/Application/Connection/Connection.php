<?php

namespace App\Application\Connection;


use App\Application\Connection\DTO\ApiRequest;
use App\Application\Connection\DTO\ApiResponse;

interface Connection
{
    public const HTTP_GET = 'GET';
    public const HTTP_POST = 'POST';
    public const HTTP_PUT = 'PUT';
    public const HTTP_DELETE = 'DELETE';

    public function reset(): void;

    public function call(): ApiResponse;

    public function configure(ApiRequest $request): void;

    public function close(): void;

}