<?php

namespace App\Application\Connection\DTO;

class DataTransferFactory
{
    public function createRequest(): ApiRequest
    {
        return new ApiRequest();
    }


}