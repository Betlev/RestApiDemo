<?php

namespace App\Application\Connection;

use App\Application\Connection\DTO\ApiRequest;

interface Auth
{
    public function sign(ApiRequest $request): void;
}